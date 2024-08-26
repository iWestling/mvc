<?php

namespace App\Service;

use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;
use App\CardGame\GameViewRenderer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

    public function advancePhaseIfNeeded(SessionInterface $session, TexasHoldemGame $game, int $currentActionIndex, int $totalPlayers): ?Response
    {
        if ($currentActionIndex >= $totalPlayers && $game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
            $game->advanceGameStage();
            $session->set('current_action_index', 0);

            if ($game->isGameOver()) {
                return $this->gameViewRenderer->renderGameView($game);
            }

            return new Response('', Response::HTTP_FOUND, ['Location' => '/proj/play']);
        }

        return null;
    }

    public function renderGameView(TexasHoldemGame $game): Response
    {
        return $this->gameViewRenderer->renderGameView($game);
    }
}
