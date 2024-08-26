<?php

namespace App\Tests\Service;

use App\Service\GameHandlerService;
use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;
use App\CardGame\GameViewRenderer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

class GameHandlerServiceTest extends TestCase
{
    /** @var GameViewRenderer&\PHPUnit\Framework\MockObject\MockObject */
    private $gameViewRenderer;

    /** @var GameHandlerService */
    private $gameHandlerService;

    /** @var TexasHoldemGame&\PHPUnit\Framework\MockObject\MockObject */
    private $game;

    /** @var PlayerActionHandler&\PHPUnit\Framework\MockObject\MockObject */
    private $playerActionHandler;

    /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    protected function setUp(): void
    {
        $this->gameViewRenderer = $this->createMock(GameViewRenderer::class);
        $this->gameHandlerService = new GameHandlerService($this->gameViewRenderer);
        $this->game = $this->createMock(TexasHoldemGame::class);
        $this->playerActionHandler = $this->createMock(PlayerActionHandler::class);
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testHandleAllInScenarioWhenAllInOccurs(): void
    {
        $this->game->/** @scrutinizer ignore-call */ method('hasAllInOccurred')->willReturn(true);
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')->willReturn(true);

        $result = $this->gameHandlerService->handleAllInScenario($this->game, $this->playerActionHandler);

        $this->assertTrue($result);
    }

    public function testHandleAllInScenarioWhenNoAllInOccurs(): void
    {
        $this->game->/** @scrutinizer ignore-call */ method('hasAllInOccurred')->willReturn(false);

        $result = $this->gameHandlerService->handleAllInScenario($this->game, $this->playerActionHandler);

        $this->assertFalse($result);
    }

    public function testAdvancePhaseIfNeededWhenPhaseAdvanced(): void
    {
        $potManager = $this->createMock(\App\CardGame\PotManager::class);
        $potManager->method('haveAllActivePlayersMatchedCurrentBet')->willReturn(true);

        $this->game->/** @scrutinizer ignore-call */ method('getPotManager')->willReturn($potManager);
        $this->game->/** @scrutinizer ignore-call */ method('getPlayers')->willReturn([]);
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')->willReturn(true);

        $this->gameViewRenderer->/** @scrutinizer ignore-call */ method('renderGameView')
            ->willReturn(new Response());

        $response = $this->gameHandlerService->advancePhaseIfNeeded($this->session, $this->game, 3, 3);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAdvancePhaseIfNeededWhenNoPhaseAdvanceNeeded(): void
    {
        $response = $this->gameHandlerService->advancePhaseIfNeeded($this->session, $this->game, 1, 3);

        $this->assertNull($response);
    }

    public function testRenderGameView(): void
    {
        $this->gameViewRenderer->/** @scrutinizer ignore-call */ method('renderGameView')
            ->willReturn(new Response());

        $response = $this->gameHandlerService->renderGameView($this->game);

        $this->assertInstanceOf(Response::class, $response);
    }
}
