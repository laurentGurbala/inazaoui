<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route("/admin/album")]
class AlbumController extends AbstractController
{
    #[Route("/", name: "admin_album_index")]
    public function index(AlbumRepository $albumRepository): Response
    {
        // Récupérer tous les albums
        $albums = $albumRepository->findAll();

        // Rendre la vue
        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    #[Route("/add", name: "admin_album_add")]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        // Créer un formulaire avec un nouvel album
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        // Traiter le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregister l'album
            $em->persist($album);
            $em->flush();

            // Rediriger vers la liste des albums
            return $this->redirectToRoute('admin_album_index');
        }

        // Rendre la vue
        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/update/{id}", name: "admin_album_update")]
    public function update(
        AlbumRepository $albumRepository,
        Request $request,
        int $id,
        EntityManagerInterface $em
    ): Response 
    {
        // Pré-remplissage du formulaire
        $album = $albumRepository->find($id);

        // Si l'album n'existe pas...
        if (!$album) {
            // Retourne une erreur 404
            throw $this->createNotFoundException('Album introuvable.');
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Rediriger vers la liste des albums 
            return $this->redirectToRoute('admin_album_index');
        }

        // Rendre la vue
        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/delete/{id}", name: "admin_album_delete")]
    public function delete(
        int $id,
        AlbumRepository $albumRepository,
        MediaRepository $mediaRepository,
        EntityManagerInterface $em
    ): Response
    {
        // Récupérer l'album en fonction de l'id
        $album = $albumRepository->find($id);
        
        if (!$album) {
            throw $this->createNotFoundException('Album introuvable.');
        }

        // Détacher les médias liés à l'album
        $medias = $mediaRepository->findBy(['album' => $album]);
        foreach ($medias as $media) {
            $media->setAlbum(null);
        }

        // Supprimer l'album
        $em->remove($album);
        $em->flush();

        // Rediriger vers la liste des albums
        return $this->redirectToRoute('admin_album_index');
    }
}
