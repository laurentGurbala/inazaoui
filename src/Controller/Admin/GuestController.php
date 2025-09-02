<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/guests')]
final class GuestController extends AbstractController
{
    #[Route('/', name: 'admin_guest_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        Request $request,
        ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $total = count($userRepository->findGuests(0, PHP_INT_MAX));


        $guests = $userRepository->findGuests($offset, $limit);

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route("/add", name: "admin_guest_add", methods: ["GET", "POST"])]
    public function add(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $user->setPassword($hasher->hashPassword($user, $plainPassword));

            $em->persist($user);
            $em->flush();

            $this->addFlash("success", "L'invité a bien été ajouté");
            return $this->redirectToRoute("admin_guest_index");
        }

        return $this->render("admin/guest/add.html.twig", [
            "form" => $form,
        ]);
    }

    #[Route("/{id}/delete", name: "admin_guest_delete", methods: ["GET"], requirements: ["id" => "\d+"])]
    public function delete(
        User $user,
        EntityManagerInterface $em,
        ): Response 
    {
        $em->remove($user);
        $em->flush();

        $this->addFlash("success", "L'invité a bien été supprimé");
        return $this->redirectToRoute("admin_guest_index");
    }

    #[Route("/admin/user/{id}/toggle-block", name: "admin_user_toggle_block", methods: ["GET"], requirements: ["id" => "\d+"])]
    public function toggleBlock(
        User $user,
        EntityManagerInterface $em
        ): Response
    {
        $user->setIsBlocked(!$user->isBlocked());
        $em->flush();

        $this->addFlash("success", $user->isBlocked() ? 'Utilisateur bloqué' : 'Utilisateur débloqué');
        return $this->redirectToRoute("admin_guest_index");
    }
}
