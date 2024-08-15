<?php

namespace App\CardGame;

class Bank
{
    /** @var array<string, int> */
    private array $bets = [];
    private int $pot;

    public function __construct()
    {
        $this->pot = 0;
    }

    public function placeBet(Player $player, int $amount): void
    {
        if (!isset($this->bets[$player->getName()])) {
            $this->bets[$player->getName()] = 0;
        }

        $this->bets[$player->getName()] += $amount;
        $this->pot += $amount;
    }

    public function getPot(): int
    {
        return $this->pot;
    }

    public function getPlayerBet(Player $player): int
    {
        return $this->bets[$player->getName()] ?? 0;
    }

    public function resetBets(): void
    {
        $this->bets = [];
    }
}
