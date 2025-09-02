<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $this->loadUser($manager);
    }

    private function loadUser(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setName("Ina Zaoui");
        $admin->setEmail("ina@zaoui.com");
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123'));
        $manager->persist($admin);

        // Create regular user
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setName("Invité " . $i);
            $user->setDescription("Je suis l'invité numéro " . $i);
            $user->setEmail("invité" . $i . "@test.fr");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($admin, '123'));
            $manager->persist($user);
        }
        $manager->flush();
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);
    }
}
