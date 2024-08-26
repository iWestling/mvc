<?php

namespace App\Service;

use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\PlayerActionInit;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GameInitializer
{
    public function initializeGame(int $chips, string $level1, string $level2): TexasHoldemGame
    {
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', $chips, 'intelligent'));
        $game->addPlayer(new Player('Computer 1', $chips, $level1));
        $game->addPlayer(new Player('Computer 2', $chips, $level2));

        $potManager = $game->getPotManager();
        $playerActionInit = new PlayerActionInit($potManager);
        $playerActionInit->initializeRoles($game->getPlayers(), 0);
        $game->getCommunityCardManager()->dealInitialCards($game->getPlayers());
        $playerActionInit->handleBlinds($game->getPlayers());

        return $game;
    }

    public function saveGameToSession(SessionInterface $session, TexasHoldemGame $game): void
    {
        $session->set('game', $game);
        $session->set('current_action_index', 0);
    }
}
