<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    private UserChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new UserChecker();
    }

    public function testCheckPreAuthWithBlockedUser(): void
    {
        $user = new User();
        $user->setIsBlocked(true);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été bloqué. Contactez l’administrateur.');

        $this->checker->checkPostAuth($user);
    }

    public function testCheckPreAuthWithActiveUser(): void
    {
        $user = new User();
        $user->setIsBlocked(false);

        // ne doit rien lancer
        $this->checker->checkPreAuth($user);

        $this->expectNotToPerformAssertions();
    }

    public function testCheckPreAuthWithDifferentUserInterface(): void
    {
        /** @var \Symfony\Component\Security\Core\User\UserInterface&\PHPUnit\Framework\MockObject\MockObject $mockUser */
        $mockUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);

        // ne doit rien lancer
        $this->checker->checkPreAuth($mockUser);

        $this->expectNotToPerformAssertions();
    }
}
