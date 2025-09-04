<?php

namespace App\Tests\Fonctional;

use App\Entity\Album;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{

    private function loginAsAdmin($client)
    {
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $admin = $userRepository->findOneBy(['email' => 'ina@zaoui.com']);
        $client->loginUser($admin);

        return $admin;
    }

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

    // Data Providers
    public function albumIndexAccessProvider(): array
    {
        return [
            'non connecté → redirection login' => [null, 302],
            'connecté simple user → 403'      => ['invite1@test.fr', 403],
            'connecté admin → 200'            => ['ina@zaoui.com', 200],
        ];
    }
}
