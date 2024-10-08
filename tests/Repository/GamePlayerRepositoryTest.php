<?php

// namespace App\Tests\Repository;

// use App\Entity\GamePlayer;
// use App\Repository\GamePlayerRepository;
// use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\ORM\Mapping\ClassMetadata;
// use Doctrine\ORM\QueryBuilder;
// use Doctrine\Persistence\ManagerRegistry;
// use PHPUnit\Framework\TestCase;

// class GamePlayerRepositoryTest extends TestCase
// {
//     private $entityManager;
//     private $repository;

//     protected function setUp(): void
//     {
//         // Mock the EntityManager and ManagerRegistry
//         $this->entityManager = $this->createMock(EntityManagerInterface::class);
//         $registry = $this->createMock(ManagerRegistry::class);

//         // Expect the repository to use the correct EntityManager and class
//         $registry->method('getManagerForClass')
//             ->with(GamePlayer::class)
//             ->willReturn($this->entityManager);

//         // Initialize the repository
//         $this->repository = new GamePlayerRepository($registry);
//     }

//     public function testDeleteAll(): void
//     {
//         // Mock the QueryBuilder and Query objects
//         $queryBuilder = $this->createMock(QueryBuilder::class);
//         $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);

//         // Set up the QueryBuilder mock to return the Query mock when 'getQuery' is called
//         $queryBuilder->method('getQuery')->willReturn($query);

//         // Expect the 'delete' method to be called on the QueryBuilder
//         $queryBuilder->expects($this->once())->method('delete')->willReturn($queryBuilder);

//         // Expect the 'execute' method to be called on the Query mock
//         $query->expects($this->once())->method('execute');

//         // Set up the EntityManager mock to return the QueryBuilder mock when 'createQueryBuilder' is called
//         $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

//         // Call the deleteAll method on the repository
//         $this->repository->deleteAll();
//     }
// }
