<?php

namespace App\Tests\Service;

use App\Service\ScoreService;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\GamePlayer;
use App\Entity\Scores;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ScoreServiceTest extends TestCase
{
    /** @var ManagerRegistry&\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var ScoreService */
    private $scoreService;

    protected function setUp(): void
    {
        // Create mocks for ManagerRegistry and EntityManagerInterface
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock the getManager method to return the EntityManager mock
        $this->doctrine->method('getManager')->willReturn($this->entityManager);

        // Initialize the ScoreService with the mocked ManagerRegistry
        $this->scoreService = new ScoreService($this->doctrine);
    }

    public function testSubmitScoreSuccessfully(): void
    {
        // Expect the entity manager to persist two entities: GamePlayer and Scores
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        // Call the submitScore method
        $response = $this->scoreService->submitScore('testuser', 30, 1000);

        // Assert that the response is a JsonResponse with a 200 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSubmitScoreFailure(): void
    {
        // Simulate an exception being thrown during the flush operation
        $this->entityManager->method('flush')->willThrowException(new Exception());

        // Call the submitScore method
        $response = $this->scoreService->submitScore('testuser', 30, 1000);

        // Assert that the response is a JsonResponse with a 500 status code
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
