<?php

namespace App\CardGame;

class CardHand
{
    /**
     * @var CardGraphic[]
     */
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * @return CardGraphic[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @return array{low: int, high: int}
     */
    public function calculateTotal(): array
    {
        $sumLow = 0; // Total score considering ace as 1
        $sumHigh = 0; // Total score considering ace as 11
        $aceCount = 0;

        foreach ($this->cards as $card) {
            $value = $card->getValue();
            if ($this->isAce($value)) {
                $sumLow += 1;
                $sumHigh += 11;
                $aceCount++;
            }
            $sumLow += min($value, 10); // Non-ace cards are valued at their face value, capped at 10
            $sumHigh += min($value, 10);
        }

        $sumHigh = $this->adjustHighScore($sumHigh, $aceCount);

        return ['low' => $sumLow, 'high' => $sumHigh];
    }

    /**
     * Check if a card value represents an ace.
     */
    private function isAce(int $value): bool
    {
        return $value === 1;
    }
    /**
     * Adjust the high score if needed.
     */
    private function adjustHighScore(int $sumHigh, int $aceCount): int
    {
        while ($aceCount > 0 && $sumHigh > 21) {
            $sumHigh -= 10; // Subtract 10 from high score for each Ace to adjust the score downwards
            $aceCount--;
        }
        return $sumHigh;
    }

    /**
     * @return array{low: int, high: int}
     */
    public function calculateTotalDealer(): array
    {
        $sumLow = 0; // Total score considering ace as 1
        $sumHigh = 0; // Total score considering ace as 11

        // Only calculate the value of first card
        if (!empty($this->cards)) {
            $firstCardValue = $this->cards[0]->getValue();

            // Handle ace value
            if ($firstCardValue === 1) {
                $sumLow += 1;
                $sumHigh += 11;
            }

            // Handle other card values
            if ($firstCardValue >= 2 && $firstCardValue <= 10) {
                $sumLow += $firstCardValue;
                $sumHigh += $firstCardValue;
            }

            // Handle face cards
            if ($firstCardValue >= 11 && $firstCardValue <= 13) {
                $sumLow += 10;
                $sumHigh += 10;
            }
        }

        return ['low' => $sumLow, 'high' => $sumHigh];
    }

}
