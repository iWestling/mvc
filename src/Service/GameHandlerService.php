<?php

namespace App\Service;

use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;
use App\CardGame\GameViewRenderer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;

class GameHandlerService
{
    private GameViewRenderer $gameViewRenderer;

    public function __construct(GameViewRenderer $gameViewRenderer)
    {
        $this->gameViewRenderer = $gameViewRenderer;
    }

    public function handleAllInScenario(TexasHoldemGame $game, PlayerActionHandler $playerActionHandler): bool
    {
        if ($game->hasAllInOccurred()) {
            $game->handleRemainingPlayersAfterAllIn($playerActionHandler);

            while (!$game->isGameOver()) {
                $game->advanceGameStage();
            }

            return true;
        }

        return false;
    }

    public function advancePhaseIfNeeded(SessionInterface $session, TexasHoldemGame $game, int $currentActionIndex, int $totalPlayers): ?string
    {
        if ($currentActionIndex >= $totalPlayers && $game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
            $game->advanceGameStage();
            $session->set('current_action_index', 0);

            if ($game->isGameOver()) {
                return 'game_over';  // Game is over, trigger rendering the final view.
            }

            return 'phase_advanced';  // Phase advanced, require redirection to /proj/play.
        }

        return null;
    }

    public function handleGameStatus(TexasHoldemGame $game, ?string $status): Response
    {
        if ($status === 'game_over') {
            return $this->renderGameView($game);
        }

        if ($status === 'phase_advanced') {
            return new Response('', Response::HTTP_FOUND, ['Location' => '/proj/play']);
        }

        return $this->renderGameView($game);
    }

    public function renderGameView(TexasHoldemGame $game): Response
    {
        return $this->gameViewRenderer->renderGameView($game);
    }
}
