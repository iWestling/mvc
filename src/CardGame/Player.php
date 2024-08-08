<?php

namespace App\CardGame;

class Player
{
    private string $name;
    private array $hand;
    private int $chips;
    private $strategy;
    private bool $folded;
    private int $currentBet;

    public function __construct(string $name, int $chips, string $level)
    {
        $this->name = $name;
        $this->hand = [];
        $this->chips = $chips;
        $this->strategy = $this->getStrategy($level);
        $this->folded = false;
        $this->currentBet = 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHand(): array
    {
        return $this->hand;
    }

    public function addCardToHand(CardGraphic $card): void
    {
        $this->hand[] = $card;
    }

    public function getChips(): int
    {
        return $this->chips;
    }

    public function bet(int $amount): void
    {
        $this->chips -= $amount;
        $this->currentBet += $amount;
    }

    public function call(int $amount): void
    {
        $this->bet($amount - $this->currentBet);
    }

    public function fold(): void
    {
        $this->folded = true;
    }

    public function isFolded(): bool
    {
        return $this->folded;
    }

    public function raise(int $amount): void
    {
        $this->bet($amount);
    }

    public function check(): void
    {
        // A check means the player bets nothing but does not fold, essentially passing the action.
    }

    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    public function resetCurrentBet(): void
    {
        $this->currentBet = 0;
    }

    private function getStrategy(string $level)
    {
        return match ($level) {
            'intelligent' => new IntelligentPlayer(),
            'normal' => new NormalPlayer(),
            default => throw new \InvalidArgumentException("Invalid level: $level"),
        };
    }

    public function makeDecision(array $communityCards, int $currentBet): string
    {
        return $this->strategy->makeDecision($this, $communityCards, $currentBet);
    }

    public function resetHand(): void
    {
        $this->hand = [];
    }

    public function addChips(int $amount): void
    {
        $this->chips += $amount;
    }

    public function unfold(): void
    {
        $this->folded = false;
    }
}
