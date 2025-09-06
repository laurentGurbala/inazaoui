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
        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $total = count($userRepository->findGuests(0, PHP_INT_MAX));

        // Récupérer les invités avec pagination
        $guests = $userRepository->findGuests($offset, $limit);

        // Rendre la vue avec les invités et les informations de pagination
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
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response
    {
        // Créer un utilisateur et un formulaire
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Traiter le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('password')->getData();
            $user->setPassword($hasher->hashPassword($user, $plainPassword));
            // Enregistrer l'utilisateur
            $em->persist($user);
            $em->flush();

            // Rediriger avec un message de succès
            $this->addFlash("success", "L'invité a bien été ajouté");
            // Rediriger vers la liste des invités
            return $this->redirectToRoute("admin_guest_index");
        }

        // Rendre le formulaire
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
        // Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        // Rediriger avec un message de succès
        $this->addFlash("success", "L'invité a bien été supprimé");
        // Rediriger vers la liste des invités
        return $this->redirectToRoute("admin_guest_index");
    }

    #[Route("/{id}/toggle-block", name: "admin_user_toggle_block", methods: ["GET"], requirements: ["id" => "\d+"])]
    public function toggleBlock(
        User $user,
        EntityManagerInterface $em
        ): Response
    {
        // Inverser le statut de blocage
        $user->setIsBlocked(!$user->isBlocked());
        $em->flush();

        // Rediriger avec un message de succès
        $this->addFlash("success", $user->isBlocked() ? 'Utilisateur bloqué' : 'Utilisateur débloqué');
        // Rediriger vers la liste des invités
        return $this->redirectToRoute("admin_guest_index");
    }
}
