<?php

namespace App\Tests\Service;

use App\Service\GameHandlerService;
use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;
use App\CardGame\GameViewRenderer;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

class GameHandlerServiceTest extends TestCase
{
    /**
     * @var GameViewRenderer|MockObject
     */
    private $gameViewRenderer;

    /**
     * @var GameHandlerService
     */
    private $gameHandlerService;

    /**
     * @var TexasHoldemGame|MockObject
     */
    private $game;

    /**
     * @var PlayerActionHandler|MockObject
     */
    private $playerActionHandler;

    /**
     * @var SessionInterface|MockObject
     */
    private $session;

    protected function setUp(): void
    {
        // Create mock objects for dependencies
        $this->gameViewRenderer = $this->createMock(GameViewRenderer::class);
        $this->gameHandlerService = new GameHandlerService($this->gameViewRenderer);
        $this->game = $this->createMock(TexasHoldemGame::class);
        $this->playerActionHandler = $this->createMock(PlayerActionHandler::class);
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testHandleAllInScenarioWhenAllInOccurs(): void
    {
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('hasAllInOccurred')->willReturn(true);
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')->willReturn(true);

        // @phpstan-ignore-next-line
        $result = $this->gameHandlerService->handleAllInScenario($this->game, $this->playerActionHandler);

        $this->assertTrue($result);
    }

    public function testHandleAllInScenarioWhenNoAllInOccurs(): void
    {
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('hasAllInOccurred')->willReturn(false);

        // @phpstan-ignore-next-line
        $result = $this->gameHandlerService->handleAllInScenario($this->game, $this->playerActionHandler);

        $this->assertFalse($result);
    }

    public function testAdvancePhaseIfNeededWhenPhaseAdvanced(): void
    {
        $potManager = $this->createMock(\App\CardGame\PotManager::class);

        $potManager->/** @scrutinizer ignore-call */ method('haveAllActivePlayersMatchedCurrentBet')->willReturn(true);
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('getPotManager')->willReturn($potManager);
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')->willReturn(false);
        // @phpstan-ignore-next-line
        $status = $this->gameHandlerService->advancePhaseIfNeeded($this->session, $this->game, 3, 3);

        $this->assertEquals('phase_advanced', $status);
    }

    public function testAdvancePhaseIfNeededWhenGameOver(): void
    {
        $potManager = $this->createMock(\App\CardGame\PotManager::class);

        $potManager->/** @scrutinizer ignore-call */ method('haveAllActivePlayersMatchedCurrentBet')->willReturn(true);
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('getPotManager')->willReturn($potManager);
        // @phpstan-ignore-next-line
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')->willReturn(true);
        // @phpstan-ignore-next-line
        $status = $this->gameHandlerService->advancePhaseIfNeeded($this->session, $this->game, 3, 3);

        $this->assertEquals('game_over', $status);
    }

    public function testAdvancePhaseIfNeededWhenNoPhaseAdvanceNeeded(): void
    {
        // @phpstan-ignore-next-line
        $status = $this->gameHandlerService->advancePhaseIfNeeded($this->session, $this->game, 1, 3);

        $this->assertNull($status);
    }

    public function testHandleGameStatusWhenGameOver(): void
    {
        // @phpstan-ignore-next-line
        $this->gameViewRenderer->/** @scrutinizer ignore-call */ method('renderGameView')->willReturn(new Response());
        // @phpstan-ignore-next-line
        $response = $this->gameHandlerService->handleGameStatus($this->game, 'game_over');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testHandleGameStatusWhenPhaseAdvanced(): void
    {
        // @phpstan-ignore-next-line
        $response = $this->gameHandlerService->handleGameStatus($this->game, 'phase_advanced');

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/proj/play', $response->headers->get('Location'));
    }

    public function testHandleGameStatusWhenStatusIsNull(): void
    {
        // @phpstan-ignore-next-line
        $this->gameViewRenderer->/** @scrutinizer ignore-call */ method('renderGameView')->willReturn(new Response());
        // @phpstan-ignore-next-line
        $response = $this->gameHandlerService->handleGameStatus($this->game, null);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRenderGameView(): void
    {
        // @phpstan-ignore-next-line
        $this->gameViewRenderer->/** @scrutinizer ignore-call */ method('renderGameView')->willReturn(new Response());
        // @phpstan-ignore-next-line
        $response = $this->gameHandlerService->renderGameView($this->game);

        $this->assertInstanceOf(Response::class, $response);
    }
}
