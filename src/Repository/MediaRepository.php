<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findAllVisibleMedias(array $criteria = [], array $orderBy = [], int $limit = 0, int $offset = 0)
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->andWhere('u.isBlocked = false');

        // ajouter éventuellement les critères
        foreach ($criteria as $field => $value) {
            $qb->andWhere("m.$field = :$field")
                ->setParameter($field, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy("m.$field", $direction);
            }
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }


    public function countVisibleMedias(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.user', 'u')
            ->andWhere('u.isBlocked = false');

        // appliquer les critères éventuels
        foreach ($criteria as $field => $value) {
            $qb->andWhere("m.$field = :$field")
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    //    /**
    //     * @return Media[] Returns an array of Media objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Media
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
