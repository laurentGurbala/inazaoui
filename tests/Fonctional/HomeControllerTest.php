<?php

namespace App\Tests\Fonctional;

use App\Tests\BaseTestCase;

class HomeControllerTest extends BaseTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Photographe');
    }

    public function testGuestsPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/guests');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Invités');
    }

    public function testGuestPageNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/guest/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @dataProvider guestPageProvider
     */
    public function testGuestPage(int $id, int $expectedStatusCode): void
    {
        $client = static::createClient();
        $client->request('GET', '/guest/' . $id);

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public function testGuestPageBlockedUser(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->loginAsUser($client);
        $user->setIsBlocked(true);
        $em->flush();

        $client->request('GET', '/guest/' . $user->getId());

        $this->assertResponseStatusCodeSame(404);

        $user->setIsBlocked(false);
        $em->flush();
    }

    public function testPortfolioPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Portfolio');
    }

    public function testAboutPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Qui suis-je ?');
    }

    // DATA PROVIDERS
    public function guestPageProvider(): array
    {
        return [
            'non existing guest' => [999999, 404],
            'existing guest'     => [2, 200],
        ];
    }
}
