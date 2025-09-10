<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUser($manager);
    }

    private function loadUser(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Create admin user
        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail('ina@zaoui.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123'));
        $manager->persist($admin);

        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Create regular user
        for ($i = 1; $i <= 100; ++$i) {
            $user = new User();
            $user->setName($faker->name());
            $user->setDescription($faker->realTextBetween(80, 200));
            $user->setEmail("invite{$i}@test.fr");
            $user->setPassword($this->passwordHasher->hashPassword($user, '123'));
            $manager->persist($user);

            // on garde des références pour MediaFixtures
            $this->addReference("user_$i", $user);
        }

        $manager->flush();
    }
}
