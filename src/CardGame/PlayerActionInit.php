<?php

namespace App\CardGame;

use App\CardGame\Player;
use App\CardGame\PotManager;
use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;

class PlayerActionInit
{
    private PotManager $potManager;

    public function __construct(PotManager $potManager)
    {
        $this->potManager = $potManager;
    }

    /**
     * Handle the blinds for the players.
     *
     * @param Player[] $players Array of Player objects
     */
    public function handleBlinds(array $players): void
    {
        foreach ($players as $player) {
            if ($player->getRole() === 'small blind') {
                $this->handleRaise($player, 2);  // Small blind bets
                continue;
            }

            if ($player->getRole() === 'big blind') {
                $this->handleRaise($player, 4);  // Big blind bets
            }
        }
    }


    public function handleCall(Player $player): void
    {
        $currentBet = $this->potManager->getCurrentBet();
        $amountToCall = min($currentBet - $player->getCurrentBet(), $player->getChips());

        $this->deductChips($player, $amountToCall);
        $this->potManager->addToPot($amountToCall);
    }


    public function handleRaise(Player $player, int $amount): void
    {
        $this->deductChips($player, $amount);
        $this->potManager->updateCurrentBet($amount);
        $this->potManager->addToPot($amount);
    }

    public function handleCheck(Player $player): void
    {
        // Suppress the unused parameter warning
        // A check doesn't involve any chips or pot updates
        unset($player);
    }

    public function handleAllIn(Player $player): void
    {
        $amountToBet = $player->getChips(); // Bet all remaining chips
        $this->deductChips($player, $amountToBet);
        $this->potManager->addToPot($amountToBet);

        // Ensure the current bet reflects the all-in amount
        $currentBet = $this->potManager->getCurrentBet();
        if ($amountToBet > $currentBet) {
            $this->potManager->updateCurrentBet($amountToBet);
        }
    }

    public function resetCurrentBet(Player $player): void
    {
        $player->setCurrentBet(0); // Assuming you have a setCurrentBet() method
    }

    private function deductChips(Player $player, int $amount): void
    {
        $playerChips = $player->getChips();

        // Ensure the player doesn't end up with negative chips
        $deduction = min($amount, $playerChips);
        $player->setChips($playerChips - $deduction);

        // Update the player's current bet
        $player->setCurrentBet($player->getCurrentBet() + $deduction);

        dump($player->getName() . " has " . $player->getChips() . " chips left after deducting " . $deduction);
    }

    /**
     * Initialize roles for the players (Dealer, Small Blind, Big Blind).
     *
     * @param Player[] $players Array of Player objects
     * @param int $dealerIndex Index of the dealer in the players array
     */
    public function initializeRoles(array $players, int $dealerIndex): void
    {
        $playerCount = count($players);
        $smallBlindIndex = ($dealerIndex + 1) % $playerCount;
        $bigBlindIndex = ($dealerIndex + 2) % $playerCount;

        foreach ($players as $index => $player) {
            if ($index === $dealerIndex) {
                $player->setRole('dealer');
                continue;
            }
            if ($index === $smallBlindIndex) {
                $player->setRole('small blind');
                continue;
            }
            if ($index === $bigBlindIndex) {
                $player->setRole('big blind');
                continue;
            }
            $player->setRole(null);
        }
    }

    public function rotateRoles(int &$dealerIndex, int $playerCount): void
    {
        $dealerIndex = ($dealerIndex + 1) % $playerCount;
    }

    /**
     * Reset player states for the new round.
     *
     * @param Player[] $players Array of Player objects
     * @param string[] $actions Array of actions corresponding to players
     */
    public function resetPlayersForNewRound(array $players, array &$actions): void
    {
        foreach ($players as $player) {
            if ($player->getChips() < 20) {
                $player->resetHand();
                $player->fold();
                $actions[$player->getName()] = "Player has insufficient chips, you'll need to start a new game.";
                continue;
            }
            $actions[$player->getName()] = 'No action yet';
            $player->resetHand();
            $player->unfold();
        }
    }


}
