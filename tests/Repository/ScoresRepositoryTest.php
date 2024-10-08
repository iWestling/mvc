<?php

// namespace App\Tests\Repository;

// use App\Entity\Scores;
// use App\Repository\ScoresRepository;
// use Doctrine\ORM\AbstractQuery;
// use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\ORM\Mapping\ClassMetadata;
// use Doctrine\ORM\QueryBuilder;
// use Doctrine\Persistence\ManagerRegistry;
// use PHPUnit\Framework\TestCase;

// class ScoresRepositoryTest extends TestCase
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
//             ->with(Scores::class)
//             ->willReturn($this->entityManager);

//         // Initialize the repository
//         $this->repository = new ScoresRepository($registry);
//     }

//     public function testFindAllWithPlayersOrderedByScore(): void
//     {
//         // Mock the QueryBuilder and Query objects
//         $queryBuilder = $this->createMock(QueryBuilder::class);
//         $query = $this->createMock(AbstractQuery::class);

//         // Mock expected result set
//         $expectedResults = [
//             ['idn' => 1, 'score' => 200, 'date' => new \DateTime(), 'username' => 'Test Player 1', 'age' => 30],
//             ['idn' => 2, 'score' => 150, 'date' => new \DateTime(), 'username' => 'Test Player 2', 'age' => 25],
//         ];

//         // Set up the Query mock to return expected results when 'getResult' is called
//         $query->method('getResult')->willReturn($expectedResults);

//         // Set up the QueryBuilder mock to return the Query mock when 'getQuery' is called
//         $queryBuilder->method('getQuery')->willReturn($query);

//         // Mock the QueryBuilder methods to chain them as expected in the repository method
//         $queryBuilder->method('select')->willReturn($queryBuilder);
//         $queryBuilder->method('join')->willReturn($queryBuilder);
//         $queryBuilder->method('orderBy')->willReturn($queryBuilder);

//         // Set up the EntityManager mock to return the QueryBuilder mock when 'createQueryBuilder' is called
//         $this->entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

//         // Call the repository method
//         $result = $this->repository->findAllWithPlayersOrderedByScore();

//         // Assert that the result matches the expected data
//         $this->assertSame($expectedResults, $result);
//     }

//     public function testDeleteAll(): void
//     {
//         // Mock the QueryBuilder and Query objects
//         $queryBuilder = $this->createMock(QueryBuilder::class);
//         $query = $this->createMock(AbstractQuery::class);

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
