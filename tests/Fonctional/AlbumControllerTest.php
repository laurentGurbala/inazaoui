<?php

namespace App\Tests\Fonctional;

use App\Entity\Album;
use App\Entity\User;
use App\Tests\BaseTestCase;

class AlbumControllerTest extends BaseTestCase
{
    /**
     * @dataProvider albumIndexAccessProvider
     */
    public function testAlbumIndexAccess(?string $userEmail, int $expectedStatusCode): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);

        if ($userEmail) {
            $user = $userRepository->findOneBy(['email' => $userEmail]);
            $client->loginUser($user);
        }

        $client->request('GET', '/admin/album/');

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public function testAddPageAccessDeniedForNonAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'invite1@test.fr']);
        $client->loginUser($user);

        $client->request('GET', '/admin/album/add');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddPageFormDisplayedForAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/album/add');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="album[name]"]');
    }

    public function testAddAlbumValidSubmission(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/album/add');

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'Nouvel Album Test',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/album/');

        // Vérifier que l’album a bien été enregistré en BDD
        $em = static::getContainer()->get('doctrine')->getManager();
        $album = $em->getRepository(Album::class)->findOneBy(['name' => 'Nouvel Album Test']);
        $this->assertNotNull($album);
    }

    public function testAlbumUpdate(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/album/update/1');

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Nouvel Album Test',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/album/');

        // Vérification en BDD
        $album = static::getContainer()->get('doctrine')->getRepository(Album::class)->find(1);
        $this->assertEquals('Nouvel Album Test', $album->getName());
    }

    public function testUpdateNonExistentAlbum(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/album/update/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteAlbum(): void
    {
        // Connection
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Création d'un album
        $album = new Album();
        $album->setName('Album à supprimer');

        // Création des médias
        $media1 = new \App\Entity\Media();
        $media1->setTitle('Media 1');
        $media1->setPath('media1.jpg');
        $media1->setUser($this->loginAsAdmin($client));
        $media1->setAlbum($album);

        $media2 = new \App\Entity\Media();
        $media2->setTitle('Media 2');
        $media2->setPath('media2.jpg');
        $media2->setUser($this->loginAsAdmin($client));
        $media2->setAlbum($album);

        // Persistance en BDD
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($album);
        $em->persist($media1);
        $em->persist($media2);
        $em->flush();

        $albumId = $album->getId();
        $media1Id = $media1->getId();
        $media2Id = $media2->getId();

        // Vérification que l'album et les médias existent en BDD
        $this->assertNotNull($em->getRepository(Album::class)->find($albumId));
        $this->assertNotNull($em->getRepository(\App\Entity\Media::class)->find($media1Id));
        $this->assertNotNull($em->getRepository(\App\Entity\Media::class)->find($media2Id));

        // Suppression de l'album
        $client->request('GET', '/admin/album/delete/'.$albumId);
        $this->assertResponseRedirects('/admin/album/');

        // Vérification en BDD
        $this->assertNull($em->getRepository(Album::class)->find($albumId));

        // Vérification que les médias n'ont plus d'album associé
        $this->assertNull($media1->getAlbum());
        $this->assertNull($media2->getAlbum());
    }

    // Data Providers
    /**
     * @return array<string, array{0: string|null, 1: int}>
     */
    public function albumIndexAccessProvider(): array
    {
        return [
            'non connecté → redirection login' => [null, 302],
            'connecté simple user → 403' => ['invite1@test.fr', 403],
            'connecté admin → 200' => ['ina@zaoui.com', 200],
        ];
    }
}
