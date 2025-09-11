<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Test la page de login.
     */
    public function testLoginPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    /**
     * Test la connexion avec des identifiants valides.
     */
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'ina@zaoui.com',
            '_password' => '123',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/');

        $client->followRedirect();

        /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage */
        $tokenStorage = self::getContainer()->get('security.token_storage');

        /** @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface|null $token */
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw new \LogicException('Aucun token trouvé.');
        }

        /** @var \App\Entity\User $user */
        $user = $token->getUser();

        $this->assertEquals('ina@zaoui.com', $user->getEmail());
    }

    /**
     * Test la connexion avec des identifiants invalides.
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'invalid@user.com',
            '_password' => 'wrongpassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }
}
