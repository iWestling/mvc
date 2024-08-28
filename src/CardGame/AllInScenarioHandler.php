<?php

namespace App\CardGame;

use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;

class AllInScenarioHandler
{
    public function handleAllInScenario(TexasHoldemGame $game, PlayerActionHandler $playerActionHandler): bool
    {
        // check if all-in, ensure all players have acted
        if ($game->hasAllInOccurred()) {
            // ensure remaining players have had a chance to call or fold
            $game->handleRemainingPlayersAfterAllIn($playerActionHandler);

            while (!$game->isGameOver()) {
                $game->advanceGameStage();
            }

            return true;
        }

        return false;
    }
}
