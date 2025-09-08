<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Test la page de login
     */
    public function testLoginPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/login");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    /**
     * Test la connexion avec des identifiants valides
     */
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/login");

        $form = $crawler->selectButton("Connexion")->form([
            "_username" => "ina@zaoui.com",
            "_password" => "123",
        ]);

        $client->submit($form);
        $this->assertResponseRedirects("/");

        $client->followRedirect();
        $user = self::getContainer()->get('security.token_storage')->getToken()->getUser();
        $this->assertEquals('ina@zaoui.com', $user->getEmail());
    }

    /**
     * Test la connexion avec des identifiants invalides
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/login");

        $form = $crawler->selectButton("Connexion")->form([
            "_username" => "invalid@user.com",
            "_password" => "wrongpassword",
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorTextContains(".alert-danger", "Invalid credentials.");
    }
}