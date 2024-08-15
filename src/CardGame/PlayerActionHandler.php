<?php

namespace App\CardGame;

use App\CardGame\Player;
use App\CardGame\PotManager;

class PlayerActionHandler
{
    private PotManager $potManager;

    public function __construct(PotManager $potManager)
    {
        $this->potManager = $potManager;
    }

    public function handleCall(Player $player): void
    {
        $amountToCall = $this->potManager->getCurrentBet() - $player->getCurrentBet();
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
        $this->potManager->updateCurrentBet($amountToBet);
    }

    public function resetCurrentBet(Player $player): void
    {
        $player->setCurrentBet(0); // Assuming you have a setCurrentBet() method
    }

    private function deductChips(Player $player, int $amount): void
    {
        $player->setChips($player->getChips() - $amount); // Assuming you have a setChips() method
        $player->setCurrentBet($player->getCurrentBet() + $amount); // Update the current bet
    }
}
