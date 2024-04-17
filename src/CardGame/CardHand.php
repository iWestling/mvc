<?php

namespace App\CardGame;

class CardHand
{
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
    }

    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    public function getCards(): array
    {
        return $this->cards;
    }
    public function calculateTotal(): array
    {
        $sumLow = 0; // Total score considering A as 1
        $sumHigh = 0; // Total score considering A as 11
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

    public function calculateTotalDealer(): array
    {
        $sumLow = 0; // Total score considering A as 1
        $sumHigh = 0; // Total score considering A as 11
    
        // Only calculate the value of first card
        if (!empty($this->cards)) {
            $firstCardValue = $this->cards[0]->getValue();
    
            // Handle Ace value
            if ($firstCardValue === 1) {
                $sumLow += 1;
                $sumHigh += 11;
            } elseif ($firstCardValue >= 2 && $firstCardValue <= 10) {
                $sumLow += $firstCardValue;
                $sumHigh += $firstCardValue;
            } else {
                $sumLow += 10;
                $sumHigh += 10;
            }
        }
    
        return ['low' => $sumLow, 'high' => $sumHigh];
    }
    
    
}
