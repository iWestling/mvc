<?php

namespace App\Card;

class DeckOfCards
{
    /**
     * @var Card[]
     */
    private array $cards;

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

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }
}
