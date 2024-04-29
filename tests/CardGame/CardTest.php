<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    public function testGetValue(): void
    {
        $card = new Card(5, 'hearts');
        $this->assertEquals(5, $card->getValue());
    }

    public function testGetCardName(): void
    {
        $card1 = new Card(1, 'hearts');
        $card2 = new Card(11, 'diamonds');
        $card3 = new Card(12, 'spades');
        $card4 = new Card(13, 'clubs');
        $this->assertEquals('ace', $card1->getCardName());
        $this->assertEquals('jack', $card2->getCardName());
        $this->assertEquals('queen', $card3->getCardName());
        $this->assertEquals('king', $card4->getCardName());
    }

    public function testGetSuit(): void
    {
        $card = new Card(5, 'hearts');
        $this->assertEquals('hearts', $card->getSuit());
    }
    public function testGetCardNameDefault(): void
    {
        $card = new Card(7, 'diamonds');
        $this->assertEquals('7', $card->getCardName());
    }

}
