<?php

namespace App\Tests\Helpers;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait TestHelpersTrait
{
    /**
     * Connecte un client en tant qu’administrateur.
     *
     * @return User L’utilisateur connecté
     */
    protected function loginAsAdmin(KernelBrowser $client): User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->getRepository(User::class);

        $admin = $userRepository->findOneBy(['email' => 'ina@zaoui.com']);
        if (!$admin) {
            throw new \LogicException('Utilisateur administrateur introuvable pour le test.');
        }

        $client->loginUser($admin);

        return $admin;
    }

    /**
     * Connecte un client en tant qu’utilisateur standard.
     *
     * @return User L’utilisateur connecté
     */
    protected function loginAsUser(KernelBrowser $client): User
    {
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => 'invite1@test.fr']);
        if (!$user) {
            throw new \LogicException('Utilisateur introuvable pour le test.');
        }

        $client->loginUser($user);

        return $user;
    }

    /**
     * Retourne le service Doctrine.
     */
    protected function getDoctrine(): ManagerRegistry
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');

        return $doctrine;
    }

    /**
     * Retourne l’EntityManager.
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();

        return $em;
    }

    /**
     * Retourne le repository d’une entité.
     *
     * @template TEntityClass of object
     *
     * @param class-string<TEntityClass> $class
     *
     * @return ObjectRepository<TEntityClass>
     */
    protected function getRepository(string $class): ObjectRepository
    {
        /** @var ObjectRepository<TEntityClass> $repo */
        $repo = $this->getDoctrine()->getRepository($class);

        return $repo;
    }
}
