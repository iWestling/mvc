<?php

namespace App\CardGame;

use App\CardGame\IntelligentComputer;
use App\CardGame\NormalComputer;
use InvalidArgumentException;

class Player
{
    private string $name;

    /** @var CardGraphic[] */
    private array $hand;

    private int $chips;

    /** @var IntelligentComputer|NormalComputer */
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
    public function setChips(int $chips): void
    {
        $this->chips = $chips;
    }

    public function setCurrentBet(int $currentBet): void
    {
        $this->currentBet = $currentBet;
    }

    /**
     * @return CardGraphic[]
     */
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

    public function fold(): void
    {
        $this->folded = true;
    }

    public function isFolded(): bool
    {
        return $this->folded;
    }

    public function getCurrentBet(): int
    {
        return $this->currentBet;
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

    /**
     * @return IntelligentComputer|NormalComputer
     */
    private function getStrategy(string $level)
    {
        return match ($level) {
            'intelligent' => new IntelligentComputer(),
            'normal' => new NormalComputer(),
            default => throw new InvalidArgumentException("Invalid level: $level"),
        };
    }

    /**
     * @param CardGraphic[] $communityCards
     * @return string
     */
    public function makeDecision(array $communityCards, int $currentBet): string
    {
        return $this->strategy->makeDecision($this, $communityCards, $currentBet);
    }
}
