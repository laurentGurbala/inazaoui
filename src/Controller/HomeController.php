<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    public function home()
    {
        // Rendu de la vue
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests")]
    public function guests(UserRepository $userRepository)
    {
        // Récupère les invités non-bloqués
        $guests = $userRepository->findVisibleGuests();

        // Rendu de la vue avec les invités
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    #[Route("/guest/{id}", name: "guest")]
    public function guest(int $id, UserRepository $userRepository)
    {
        // On récupère l'invité par son id
        $guest = $userRepository->find($id);

        // Si l'invité n'existe pas ou qu'il est bloqué...
        if (!$guest || $guest->isBlocked()) {
            // Affiche une erreur 404
            throw $this->createNotFoundException('Cet invité n’existe pas ou a été bloqué.');
        }

        // Rendu de la vue avec l'invité
        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route("/portfolio/{id}", name: "portfolio")]
    public function portfolio(
        AlbumRepository $albumRepository,
        MediaRepository $mediaRepository,
        UserRepository $userRepository,
        ?int $id = null
    ) {
        // Récupèr les albums, l'album courant et les médias 
        $albums = $albumRepository->findAll();
        $album = $id ? $albumRepository->find($id) : null;
        $user = $userRepository->findAdmin();

        // Si l'album n'existe pas et qu'un id est fourni
        $medias = $album ? $mediaRepository->findByAlbum($album) : $mediaRepository->findByUser($user);

        // Rendu de la vue
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route("/about", name: "about")]
    public function about()
    {
        // Rendu de la vue
        return $this->render('front/about.html.twig');
    }
}
