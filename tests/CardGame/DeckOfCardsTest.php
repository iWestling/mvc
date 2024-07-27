<?php

namespace App\Tests\CardGame;

use App\CardGame\DeckOfCards;
use PHPUnit\Framework\TestCase;

class DeckOfCardsTest extends TestCase
{
    public function testGetDeck(): void
    {
        $deck = new DeckOfCards();
        $this->assertCount(52, $deck->getDeck());
    }
}
