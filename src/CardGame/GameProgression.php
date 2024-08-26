<?php

namespace App\CardGame;

use App\CardGame\TexasHoldemGame;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\CardGame\GameViewRenderer;

class GameProgression
{
    private GameViewRenderer $gameViewRenderer;

    public function __construct(GameViewRenderer $gameViewRenderer)
    {
        $this->gameViewRenderer = $gameViewRenderer;
    }

    public function advancePhaseIfNeeded(SessionInterface $session, TexasHoldemGame $game, int $currentActionIndex, int $totalPlayers): ?Response
    {
        if ($currentActionIndex >= $totalPlayers && $game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
            // If all players have matched the current bet, advance to the next phase
            $game->advanceGameStage();

            // Reset the action index for the next phase
            $session->set('current_action_index', 0);

            // Check if the game is over and render the final state if it is
            if ($game->isGameOver()) {
                return $this->gameViewRenderer->renderGameView($game);
            }

            return new Response('', Response::HTTP_FOUND, ['Location' => '/proj/play']);
        }

        return null; // Return null if no phase advancement is needed
    }
}
