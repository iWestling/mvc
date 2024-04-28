<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class DeckOfCardsTest extends TestCase
{
    public function testGetDeck()
    {
        $deck = new DeckOfCards();
        $this->assertCount(52, $deck->getDeck());
    }
}
