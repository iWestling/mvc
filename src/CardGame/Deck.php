<?php

namespace App\CardGame;

class Deck
{
    private array $cards;

    public function __construct()
    {
        $this->cards = [];
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        foreach ($suits as $suit) {
            for ($value = 1; $value <= 13; $value++) {
                $this->cards[] = new CardGraphic($value, $suit);
            }
        }
        shuffle($this->cards);
    }

    public function drawCard(): ?CardGraphic
    {
        return array_shift($this->cards);
    }

    public function getCards(): array
    {
        return $this->cards;
    }
}
