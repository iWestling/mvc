<?php

namespace App\Card;

class DeckOfCards
{
    public static function generateDeck(): array
    {
        $deck = [];
        for ($i = 1; $i <= 52; $i++) {
            $deck[] = new Card($i);
        }
        return $deck;
    }
}
