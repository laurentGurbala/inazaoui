<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends BaseTestCase
{
    /**
     * Test de l'accès à la liste des médias en tant qu'administrateur
     */
    public function testIndexAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/media/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
        $this->assertGreaterThan(
            0,
            $crawler->filter('table tbody tr')->count(),
            'Admin devrait voir des médias'
        );
    }

    /**
     * Test de l'accès à la liste des médias en tant qu'utilisateur standard
     */
    public function testIndexAsUser(): void
    {
        $client = static::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', '/admin/media/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');

        $rows = $crawler->filter('table tbody tr');
        $this->assertGreaterThan(0, $rows->count());
    }

    /**
     * Test de l'accès à la liste des médias en tant qu'utilisateur anonyme
     */
    public function testIndexAsAnonymousRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/media/');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test de l'ajout d'un média en tant qu'administrateur
     */
    public function testAddAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Accès au formulaire
        $crawler = $client->request('GET', '/admin/media/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Création d’un faux fichier image
        $filePath = __DIR__ . '/Fixtures/test_image.jpg';
        copy(__DIR__ . '/Fixtures/test_image.jpg', $filePath);
        $this->assertFileExists($filePath, "Le fichier de test doit exister à l'emplacement Fixtures/test_image.jpg");
        $uploadedFile = new UploadedFile(
            $filePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        // Récupérer un album existant pour l'assigner au média
        $albumRepository = static::getContainer()->get('doctrine')->getRepository(\App\Entity\Album::class);
        $album = $albumRepository->findOneBy([]);
        $this->assertNotNull($album);

        // Récupérer un utilisateur existant pour l'assigner au média
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy([]);

        // Remplir et soumettre le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'media[title]' => 'Image de test',
            "media[user]" => $user->getId(),
            'media[album]' => $album->getId(),
        ]);

        // Soumettre le formulaire avec le fichier
        $client->submit($form, [
            'media[file]' => $uploadedFile
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/admin/media/');
        $client->followRedirect();

        // Vérifier que le média a bien été créé en BDD
        $em = static::getContainer()->get('doctrine')->getManager();
        $mediaRepo = $em->getRepository(\App\Entity\Media::class);
        $media = $mediaRepo->findOneBy(['title' => 'Image de test']);
        $this->assertNotNull($media);
        $this->assertEquals($album->getId(), $media->getAlbum()->getId());
    }

    /**
     * Test de l'ajout d'un média en tant qu'utilisateur standard
     */
    public function testAddAsUser(): void
    {
        $client = static::createClient();
        $this->loginAsUser($client);

        // Accès au formulaire
        $crawler = $client->request('GET', '/admin/media/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Création d’un faux fichier image
        $filePath = __DIR__ . '/Fixtures/test_image.jpg';
        copy(__DIR__ . '/Fixtures/test_image.jpg', $filePath);
        $this->assertFileExists($filePath, "Le fichier de test doit exister à l'emplacement Fixtures/test_image.jpg");
        $uploadedFile = new UploadedFile(
            $filePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        // Remplir et soumettre le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'media[title]' => 'Image de test',
        ]);

        // Soumettre le formulaire avec le fichier
        $client->submit($form, [
            'media[file]' => $uploadedFile
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/admin/media/');
        $client->followRedirect();

        // Vérifier que le média a bien été créé en BDD
        $em = static::getContainer()->get('doctrine')->getManager();
        $mediaRepo = $em->getRepository(\App\Entity\Media::class);
        $media = $mediaRepo->findOneBy(['title' => 'Image de test']);
        $this->assertNotNull($media);
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $em = static::getContainer()->get('doctrine')->getManager();

        // Créer un média à supprimer
        $media = new \App\Entity\Media();
        $media->setTitle('Media à supprimer');
        $media->setPath(sys_get_temp_dir() . '/test_media.jpg');
        file_put_contents($media->getPath(), 'dummy'); // créer un fichier vide
        $em->persist($media);
        $em->flush();

        $mediaId = $media->getId();
        $this->assertFileExists($media->getPath());

        // Appel de la route delete
        $client->request('GET', '/admin/media/delete/' . $mediaId);
        $this->assertResponseRedirects('/admin/media/');
        $client->followRedirect();

        // Vérification BDD
        $deletedMedia = $em->getRepository(\App\Entity\Media::class)->find($mediaId);
        $this->assertNull($deletedMedia);

        // Vérification fichier
        $this->assertFileDoesNotExist(sys_get_temp_dir() . '/test_media.jpg');
    }
}
