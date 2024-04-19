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
            if ($value >= 2 && $value <= 10) {
                $sumLow += $value;
                $sumHigh += $value;
            } elseif ($value >= 11 && $value <= 13) {
                $sumLow += 10;
                $sumHigh += 10;
            } elseif ($value === 1) {
                $sumLow += 1;
                $sumHigh += 11;
                $aceCount++;
            }
        }

        // Adjust high score if needed
        while ($aceCount > 0 && $sumHigh + 10 <= 21) {
            $sumHigh += 10;
            $aceCount--;
        }

        return ['low' => $sumLow, 'high' => $sumHigh];
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
