<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\MediaFixtures;
use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaRepositoryTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;
    private MediaRepository $mediaRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->mediaRepository = $this->entityManager->getRepository(Media::class);
    }


    public function testFindAllVisibleMediasReturnsOnlyNonBlockedUsers(): void
    {
        $medias = $this->mediaRepository->findAllVisibleMedias();

        $this->assertNotEmpty($medias);

        foreach ($medias as $media) {
            $this->assertFalse($media->getUser()->isBlocked());
        }
    }

    public function testFindAllVisibleMediasWithLimitAndOrder(): void
    {
        $medias = $this->mediaRepository->findAllVisibleMedias([], ['id' => 'DESC'], 5);

        $this->assertCount(5, $medias);
        $this->assertGreaterThan($medias[1]->getId(), $medias[0]->getId());
    }

    public function testCountVisibleMedias(): void
    {
        $count = $this->mediaRepository->countVisibleMedias();

        $this->assertGreaterThan(0, $count);
        $this->assertIsInt($count);

        // Test avec critère
        $firstMedia = $this->mediaRepository->findOneBy([]);
        $countWithCriteria = $this->mediaRepository->countVisibleMedias([
            'id' => $firstMedia->getId()
        ]);

        $this->assertEquals(1, $countWithCriteria);
    }
}
