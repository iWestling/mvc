<?php

namespace App\CardGame;

class DeckOfCards
{
    private array $deck;

    public function __construct()
    {
        $this->deck = [];
        $this->generateDeck();
    }

    private function generateDeck(): void
    {
        $suits = ['hearts', 'diamonds', 'spades', 'clubs'];
        foreach ($suits as $suit) {
            for ($value = 1; $value <= 13; $value++) {
                $cardGraphic = new CardGraphic($value, $suit);
                $this->deck[] = $cardGraphic;
            }
        }
    }
    

    public function getDeck(): array
    {
        return $this->deck;
    }
}