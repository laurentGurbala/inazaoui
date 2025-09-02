<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    public const ALBUM_REFERENCE_PREFIX = 'album-';

    public function load(ObjectManager $manager): void
    {
        $this->loadAlbum($manager);
        $this->loadMedia($manager);
    }

    private function loadAlbum(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $album = new Album();
            $album->setName('Album ' . $i);
            $manager->persist($album);

            $this->addReference(self::ALBUM_REFERENCE_PREFIX . $i, $album);
        }

        $manager->flush();
    }

    private function loadMedia(ObjectManager $manager): void
    {
        for ($i = 1; $i < 50; $i++) {
            $media = new Media();
            $media->setTitle("Titre " . $i);
            $media->setPath(sprintf('uploads/admin_media%d.jpg', $i));
            $media->setUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class));

            $albumIndex = (int) ceil($i / 10);
            $media->setAlbum(
                $this->getReference(self::ALBUM_REFERENCE_PREFIX . $albumIndex, Album::class)
            );

            $manager->persist($media);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
