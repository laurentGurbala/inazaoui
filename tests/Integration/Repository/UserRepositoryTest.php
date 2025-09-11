<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Helpers\TestHelpersTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use TestHelpersTrait;

    private UserRepository $userRepository;

    public function setUp(): void
    {
        self::bootKernel();

        /** @var UserRepository $repo */
        $repo = $this->getRepository(User::class);
        $this->userRepository = $repo;
    }

    public function testFindAdmin(): void
    {
        $admin = $this->userRepository->findAdmin();
        $this->assertNotNull($admin);
        $this->assertContains('ROLE_ADMIN', $admin->getRoles());
    }

    public function testFindGuests(): void
    {
        $guests = $this->userRepository->findGuests();
        $this->assertNotEmpty($guests);

        foreach ($guests as $guest) {
            $this->assertNotContains('ROLE_ADMIN', $guest->getRoles());
        }
    }

    public function testFindVisibleGuests(): void
    {
        $guests = $this->userRepository->findVisibleGuests();
        $this->assertNotEmpty($guests);

        foreach ($guests as $guest) {
            $this->assertFalse($guest->isBlocked());
            $this->assertNotContains('ROLE_ADMIN', $guest->getRoles());
        }
    }
}
