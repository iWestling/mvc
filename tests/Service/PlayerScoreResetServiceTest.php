<?php

namespace App\Tests\Service;

use App\Entity\GamePlayer;
use App\Entity\Scores;
use App\Repository\GamePlayerRepository;
use App\Repository\ScoresRepository;
use App\Service\PlayerScoreResetService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use DateTime;

/**
 * @method \PHPUnit\Framework\MockObject\MockObject expects($param)
 * @method \PHPUnit\Framework\MockObject\MockObject method($param)
 */
class PlayerScoreResetServiceTest extends TestCase
{
    /** @var GamePlayerRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $gamePlayerRepository;

    /** @var ScoresRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $scoresRepository;

    /** @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var PlayerScoreResetService */
    private $resetService;

    protected function setUp(): void
    {
        // Mock the GamePlayerRepository, ScoresRepository, and EntityManagerInterface
        $this->gamePlayerRepository = $this->createMock(GamePlayerRepository::class);
        $this->scoresRepository = $this->createMock(ScoresRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Initialize the service with the mocked dependencies
        $this->resetService = new PlayerScoreResetService(
            $this->gamePlayerRepository,
            $this->scoresRepository,
            $this->entityManager
        );
    }

    public function testResetPlayerDataSuccess(): void
    {
        // Mock deleteAll methods
        $this->scoresRepository->/** @scrutinizer ignore-call */ expects($this->once())->method('deleteAll');
        $this->gamePlayerRepository->/** @scrutinizer ignore-call */ expects($this->once())->method('deleteAll');

        // Mock persist and flush methods
        $this->entityManager->expects($this->exactly(6))->method('persist'); // 3 players + 3 scores
        $this->entityManager->expects($this->once())->method('flush');

        // Call the resetPlayerData method
        $response = $this->resetService->resetPlayerData();

        // Assert that the response is a success message
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Player and score database reset successful', $response->getContent());
    }

    public function testResetPlayerDataFailure(): void
    {
        // Mock deleteAll methods
        $this->scoresRepository->/** @scrutinizer ignore-call */ expects($this->once())->method('deleteAll');
        $this->gamePlayerRepository->/** @scrutinizer ignore-call */ expects($this->once())->method('deleteAll');

        // Simulate an exception being thrown during the persist operation
        $this->entityManager->/** @scrutinizer ignore-call */ method('persist')->will($this->throwException(new Exception('Test Exception')));

        // Call the resetPlayerData method
        $response = $this->resetService->resetPlayerData();

        // Assert that the response is an error message
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertStringContainsString('Error resetting player and score data: Test Exception', (string)$response->getContent());
    }
}
