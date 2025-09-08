<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/admin/media")]
class MediaController extends AbstractController
{
    #[Route("/", name: "admin_media_index")]
    public function index(Request $request, MediaRepository $mediaRepository)
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];
        $limit = 25;

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $mediaRepository->findAllVisibleMedias(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );
        $total = $mediaRepository->countVisibleMedias($criteria);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route("/add", name: "admin_media_add")]
    public function add(
        Request $request,
        EntityManagerInterface $em
    )
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());
            $em->persist($media);
            $em->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/delete/{id}", name: "admin_media_delete")]
    public function delete(
        int $id,
        MediaRepository $mediaRepository,
        EntityManagerInterface $em
        )
    {
        $media = $mediaRepository->find($id);
        $em->remove($media);
        $em->flush();
        unlink($media->getPath());

        return $this->redirectToRoute('admin_media_index');
    }
}