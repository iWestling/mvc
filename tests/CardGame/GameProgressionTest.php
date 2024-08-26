<?php

namespace App\Tests\CardGame;

use App\CardGame\GameProgression;
use App\CardGame\TexasHoldemGame;
use App\CardGame\PotManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\CardGame\GameViewRenderer;

class GameProgressionTest extends TestCase
{
    public function testAdvancePhaseWhenGameOver(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $session = $this->createMock(SessionInterface::class);
        $viewRenderer = $this->createMock(GameViewRenderer::class);

        // Mock the game behavior
        $game->method('isGameOver')->willReturn(true);
        $game->method('getPotManager')
            ->willReturn($this->createConfiguredMock(PotManager::class, [
                'haveAllActivePlayersMatchedCurrentBet' => true
            ]));

        $viewRenderer->method('renderGameView')
            ->with($game)
            ->willReturn(new Response());

        $advancer = new GameProgression($viewRenderer);

        // Call the method and assert that it returns a Response
        $response = $advancer->advancePhaseIfNeeded($session, $game, 3, 3);

        $this->assertInstanceOf(Response::class, $response);
    }


    public function testAdvancePhaseToNextStage(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $session = $this->createMock(SessionInterface::class);
        $viewRenderer = $this->createMock(GameViewRenderer::class);

        // Mock game behavior
        $game->method('isGameOver')->willReturn(false);
        $game->method('getPotManager')
            ->willReturn($this->createConfiguredMock(PotManager::class, [
                'haveAllActivePlayersMatchedCurrentBet' => true
            ]));

        $session->expects($this->once())
            ->method('set')
            ->with('current_action_index', 0);

        // Instantiate the GameProgression object
        $advancer = new GameProgression($viewRenderer);

        // Call the method and assert that it returns a RedirectResponse
        $response = $advancer->advancePhaseIfNeeded($session, $game, 3, 3);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('/proj/play', $response->headers->get('Location'));
    }


}
