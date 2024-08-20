<?php

namespace App\Repository;

use App\Entity\Scores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scores>
 */
class ScoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scores::class);
    }

    /**
     * Fetch joined data between Scores and GamePlayer, score descending.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllWithPlayersOrderedByScore(): array
    {
        $results = $this->createQueryBuilder('s')
            ->select('s.idn, s.score, s.date, g.username, g.age')
            ->join('s.userId', 'g')
            ->orderBy('s.score', 'DESC')
            ->getQuery()
            ->getResult();

        // Ensure that it always returns an array
        return is_array($results) ? $results : [];
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return Scores[] Returns an array of Scores objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Scores
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
