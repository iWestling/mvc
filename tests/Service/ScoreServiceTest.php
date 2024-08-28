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
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->doctrine->method('getManager')->willReturn($this->entityManager);

        $this->scoreService = new ScoreService($this->doctrine);
    }

    public function testSubmitScoreSuccessfully(): void
    {
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->scoreService->submitScore('testuser', 30, 1000);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testSubmitScoreFailure(): void
    {
        $this->entityManager->/** @scrutinizer ignore-call */ method('flush')->willThrowException(new Exception());

        $response = $this->scoreService->submitScore('testuser', 30, 1000);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
