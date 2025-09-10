<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Media;
use App\Repository\MediaRepository;
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

    /**
     * @dataProvider mediaProvider
     *
     * @param array<string,mixed>  $criteria
     * @param array<string,string> $orderBy
     */
    public function testFindAllVisibleMediasReturnsOnlyNonBlockedUsers(
        array $criteria,
        array $orderBy,
        int $limit,
        int $offset,
        ?int $expectedCount,
    ): void {
        $medias = $this->mediaRepository->findAllVisibleMedias($criteria, $orderBy, $limit, $offset);

        // Si expectedCount est null => on ne teste pas le nombre exact
        if (null !== $expectedCount) {
            $this->assertCount($expectedCount, $medias);
        } else {
            $this->assertNotEmpty($medias); // juste vérifier que la collection n'est pas vide
        }

        // Tous les médias doivent appartenir à des utilisateurs non bloqués
        foreach ($medias as $media) {
            $this->assertFalse($media->getUser()->isBlocked());
        }
    }

    public function testCountVisibleMedias(): void
    {
        $count = $this->mediaRepository->countVisibleMedias();

        $this->assertGreaterThan(0, $count);

        // Test avec critère
        $firstMedia = $this->mediaRepository->findOneBy([]);
        $countWithCriteria = $this->mediaRepository->countVisibleMedias([
            'id' => $firstMedia->getId(),
        ]);

        $this->assertEquals(1, $countWithCriteria);
    }

    /**
     * Fournisseur de données pour les tests de findAllVisibleMedias.
     *
     * @return array<string, array{0: array<string,mixed>, 1: array<string,string>, 2: int, 3: int, 4: int|null}>
     */
    public function mediaProvider(): array
    {
        return [
            'no limit no offset' => [[], ['id' => 'ASC'], 0, 0, null],
            'limit 5 offset 0' => [[], ['id' => 'DESC'], 5, 0, 5],
            'limit 5 offset 5' => [[], ['id' => 'ASC'], 5, 5, 5],
            'offset beyond count' => [[], [], 5, 6000, 0],
        ];
    }
}
