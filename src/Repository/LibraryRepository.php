<?php

namespace App\Repository;

use App\Entity\Library;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Library>
 * @method \PHPUnit\Framework\MockObject\Builder\InvocationMocker expects($argument)
 */
class LibraryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Library::class);
    }

    public function deleteAll(): void
    {
        $data = $this->getEntityManager();
        $query = $data->createQuery('DELETE FROM App\Entity\Library');
        $query->execute();
    }

    public function save(Library $library): void
    {
        $data = $this->getEntityManager();
        $data->persist($library);
        $data->flush();
    }

    public function remove(Library $book): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($book);
        $entityManager->flush();
    }


    //    /**
    //     * @return Library[] Returns an array of Library objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Library
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
