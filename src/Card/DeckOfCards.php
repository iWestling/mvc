<?php

namespace App\Card;

use App\Card\CardGraphic;

class DeckOfCards
{
    public static function generateDeck(): array
    {
        $deck = [];
        for ($i = 1; $i <= 52; $i++) {
            $deck[] = new CardGraphic($i);
        }
        return $deck;
    }
}
