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
    /**
     * Check if all active players have matched the current bet.
     *
     * @param Player[] $players
     * @return bool
     */
    public function haveAllActivePlayersMatchedCurrentBet(array $players): bool
    {
        foreach ($players as $player) {
            if (!$player->isFolded() && $player->getCurrentBet() < $this->currentBet) {
                return false; // If any active player has not matched the current bet
            }
        }

        return true; // All active players have matched the current bet
    }

    /**
     * Distribute the pot to a single winner.
     */
    public function distributeWinningsToPlayer(Player $player): void
    {
        $amount = $this->getPot();
        $player->addChips($amount);
    }

    /**
     * Split the pot among multiple winners.
     *
     * @param Player[] $winners
     */
    public function splitPotAmongWinners(array $winners): void
    {
        $potShare = intdiv($this->getPot(), count($winners));
        foreach ($winners as $winner) {
            $winner->addChips($potShare);
        }
    }

}
