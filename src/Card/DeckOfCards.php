<?php

namespace App\Card;

class DeckOfCards
{
    private $cards;

    public function __construct()
    {
        $this->cards = [];
        $this->generateDeck();
    }

    private function generateDeck(): void
    {
        for ($i = 1; $i <= 52; $i++) {
            $this->cards[] = new Card($i);
        }
    }

    public function getCards(): array
    {
        return $this->cards;
    }
}
