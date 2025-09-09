<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * @Route("/guests", name="guests")
     */
    public function guests(UserRepository $userRepository)
    {
        $guests = $userRepository->findBy(['admin' => false]);
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * @Route("/guest/{id}", name="guest")
     */
    public function guest(int $id,UserRepository $userRepository)
    {
        // On récupère le guest via le repository injecté
        $guest = $userRepository->find($id);
        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * @Route("/portfolio/{id}", name="portfolio", defaults={"id"=null})
     */
    public function portfolio(
        AlbumRepository $albumRepository,
        MediaRepository $mediaRepository,
        UserRepository $userRepository,
        ?int $id = null
        )
    {
        $albums = $albumRepository->findAll();
        $album = $id ? $albumRepository->find($id) : null;
        $user = $userRepository->findOneBy(['admin' => true]);

        $medias = $album ? $mediaRepository->findByAlbum($album) : $mediaRepository->findByUser($user);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}