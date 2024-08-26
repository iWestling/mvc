<?php

namespace App\CardGame;

use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;

class AllInScenarioHandler
{
    public function handleAllInScenario(TexasHoldemGame $game, PlayerActionHandler $playerActionHandler): bool
    {
        // Check if an All-In has occurred, and if so, ensure all players have acted
        if ($game->hasAllInOccurred()) {
            // Ensure remaining players have had a chance to call or fold
            $game->handleRemainingPlayersAfterAllIn($playerActionHandler);

            // After all players have acted, proceed with the game stages
            while (!$game->isGameOver()) {
                $game->advanceGameStage();
            }

            return true; // Game has ended after the All-In scenario
        }

        return false; // No All-In scenario handled
    }
}
