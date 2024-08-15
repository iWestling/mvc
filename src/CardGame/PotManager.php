<?php

namespace App\CardGame;

class PotManager
{
    private int $pot = 0;
    private int $currentBet = 0;

    public function addToPot(int $amount): void
    {
        $this->pot += $amount;
    }

    public function getPot(): int
    {
        return $this->pot;
    }

    public function resetPot(): void
    {
        $this->pot = 0;
    }

    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    public function updateCurrentBet(int $amount): void
    {
        $this->currentBet = $amount;
    }

    public function resetCurrentBet(): void
    {
        $this->currentBet = 0;
    }
}
