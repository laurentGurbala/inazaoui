<?php

namespace App\Tests\Fonctional;

use App\Entity\User;
use App\Tests\BaseTestCase;

class GuestControllerTest extends BaseTestCase
{
    /**
     * Test de la page d'index des invités
     */
    public function testGuestIndexPage(): void
    {
        // Connection en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Accès à la page des invités
        $client->request('GET', '/admin/guests/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'Nom');

        // Vérifie que la pagination affiche 50 éléments max
        $this->assertCount(
            50,
            $client->getCrawler()->filter('table tbody tr')
        );
    }

    /**
     * Test d'accès refusé pour un utilisateur non administrateur
     */
    public function testGuestIndexAccessDeniedForNonAdmin(): void
    {
        $client = static::createClient();

        // Connexion d'un utilisateur normal
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'invite1@test.fr']);
        $client->loginUser($user);

        // Tentative d'accès à la page des invités
        $client->request('GET', '/admin/guests/');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test de l'ajout d'un invité
     */
    public function testAddGuest(): void
    {
        // Connection en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Accès à la page d'ajout d'invité
        $client->request('GET', '/admin/guests/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[name]"]');

        // Récupérer le formulaire et le remplir
        $form = $client->getCrawler()->selectButton("Enregistrer")->form([
            "user[name]" => "Invité Test",
            "user[description]" => "Description de l'invité test",
            "user[email]" => "invite.test@example.com",
            "user[password]" => "123",
        ]);

        // Soumettre le formulaire
        $client->submit($form);
        $this->assertResponseRedirects('/admin/guests/');
        $client->followRedirect();

        // Vérifier que l'invité a été ajouté
        $this->assertSelectorTextContains('table tbody', 'Invité Test');

        // Vérifier que l'invité est bien dans la base de données
        $user = self::getContainer()->get('doctrine')->getRepository(User::class)
            ->findOneBy(['email' => 'invite.test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Invité Test', $user->getName());

        // Nettoyer la base de données en supprimant l'invité ajouté
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    /**
     * Test d'ajout d'un invité avec un email déjà existant
     */
    public function testAddGuestWithExistingEmail(): void
    {
        // Connection en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // On récupère un utilisateur existant pour utiliser son email
        $existingUser = self::getContainer()->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy([]);
        $this->assertNotNull($existingUser);

        // Accès à la page d'ajout d'invité
        $crawler = $client->request('GET', '/admin/guests/add');
        $this->assertResponseIsSuccessful();

        // Récupérer le formulaire et le remplir avec un email existant
        $form = $crawler->selectButton("Enregistrer")->form([
            "user[name]" => "Invité Test Duplicate",
            "user[description]" => "Description test",
            "user[email]" => $existingUser->getEmail(),
            "user[password]" => "123",
        ]);

        // Soumettre le formulaire
        $client->submit($form);
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'il y a déjà un compte avec cette adresse email'
        );
    }

    /**
     * Test d'ajout d'un invité avec des données invalides
     */
    /**
     * @dataProvider invalidGuestProvider
     *
     * @param array<string,string> $formData
     * @param string $expectedError
     */
    public function testAddGuestInvalidData(array $formData, string $expectedError): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Accès à la page d'ajout d'invité
        $crawler = $client->request('GET', '/admin/guests/add');
        $form = $crawler->selectButton('Enregistrer')->form($formData);

        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier que la réponse est 422 (validation échouée)
        $this->assertResponseStatusCodeSame(422);

        // Vérifier que le message d'erreur attendu apparaît
        $this->assertSelectorTextContains('.invalid-feedback', $expectedError);
    }

    /**
     * Fournisseur de données pour les tests d'ajout d'invité avec des données invalides
     *
     * @return array<string, array{0: array<string,string>, 1: string}>
     */
    public function invalidGuestProvider(): array
    {
        return [
            'email invalide' => [
                [
                    'user[name]' => 'Test User',
                    'user[description]' => 'Desc',
                    'user[email]' => 'email-invalide',
                    'user[password]' => '123',
                ],
                "Le format de l'email est invalide",
            ],
            'nom vide' => [
                [
                    'user[name]' => '',
                    'user[description]' => 'Desc',
                    'user[email]' => 'nouvel.email@example.com',
                    'user[password]' => '123',
                ],
                'Le nom ne peut pas être vide',
            ],
        ];
    }

    /**
     * Test de la suppression d'un invité
     */
    public function testDeleteGuest(): void
    {
        // Connection en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Ajouter un invité à supprimer
        $guest = new User();
        $guest->setName('Invité à supprimer');
        $guest->setEmail('invite.suppression@example.com');
        $guest->setPassword('123');

        // Enregistrer l'invité
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($guest);
        $entityManager->flush();
        $guestId = $guest->getId();

        // Accéder à la page de suppression de l'invité
        $client->request('GET', '/admin/guests/' . $guestId . '/delete');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/admin/guests/');

        // Vérifier le message de succès
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'invité a bien été supprimé");

        // Vérifier que l'invité a été supprimé
        $this->assertNull($entityManager->getRepository(User::class)->find($guestId));
    }

    /**
     * Test de la suppression d'un invité sans être connecté
     */
    public function testDeleteGuestRequiresLogin(): void
    {
        // Pas de connexion
        $client = static::createClient();

        // Récupérer un invité existant
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $guest = $userRepository->findOneBy([]);
        $this->assertNotNull($guest);

        // Tentative de suppression d'un invité
        $guestId = $guest->getId();
        $client->request("GET", "/admin/guests/" . $guestId . "/delete");

        // Vérifier la redirection vers la page de login
        $this->assertResponseRedirects("/login");
    }

    /**
     * Test de la suppression d'un invité en tant qu'utilisateur non administrateur
     */
    public function testDeleteGuestAccessDeniedForNonAdmin(): void
    {
        // Connexion en tant qu'utilisateur non administrateur
        $client = static::createClient();
        $this->loginAsUser($client);

        // Récupérer un invité existant
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $guest = $userRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertNotNull($guest);
        $guestId = $guest->getId();

        // Tentative de suppression
        $client->request("GET", "/admin/guests/" . $guestId . "/delete");
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test de la suppression d'un invité avec un ID non existant
     */
    public function testDeleteNonExistentGuest(): void
    {
        // Connexion en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Tentative de suppression d'un invité avec un ID non existant
        $client->request('GET', '/admin/guests/999999/delete');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test du blocage et déblocage d'un invité
     */
    public function testToggleBlock(): void
    {
        // Connexion en tant qu'administrateur
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $em = static::getContainer()->get('doctrine')->getManager();
        $userRepository = $em->getRepository(User::class);

        // On prend un invité existant
        $guest = $userRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($guest);
        $guestId = $guest->getId();

        // Toggle → devrait bloquer
        $client->request('GET', '/admin/guests/' . $guestId . '/toggle-block');
        $this->assertResponseRedirects('/admin/guests/');
        $client->followRedirect();

        $em->clear();
        $updatedGuest = $userRepository->find($guestId);
        $this->assertTrue($updatedGuest->isBlocked());
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Utilisateur bloqué');

        // Toggle à nouveau → devrait débloquer
        $client->request('GET', '/admin/guests/' . $guestId . '/toggle-block');
        $this->assertResponseRedirects('/admin/guests/');
        $client->followRedirect();

        $em->clear();
        $updatedGuest = $userRepository->find($guestId);
        $this->assertFalse($updatedGuest->isBlocked());
        $this->assertSelectorTextContains('.alert-success', 'Utilisateur débloqué');
    }
}
