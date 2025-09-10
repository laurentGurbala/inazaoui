<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected function loginAsAdmin(KernelBrowser $client): User
    {
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $admin = $userRepository->findOneBy(['email' => 'ina@zaoui.com']);
        $client->loginUser($admin);

        return $admin;
    }

    protected function loginAsUser(KernelBrowser $client): User
    {
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'invite1@test.fr']);
        $client->loginUser($user);

        return $user;
    }
}
