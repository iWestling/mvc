<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class CardGraphicTest extends TestCase
{
    public function testGetAsString(): void
    {
        $card = new CardGraphic(5, 'hearts');
        $this->assertEquals('img/carddeck/hearts_5.png', $card->getAsString());
    }

    public function testGetUnturned(): void
    {
        $card = new CardGraphic(5, 'hearts');
        $this->assertEquals('img/carddeck/unturned.png', $card->getUnturned());
    }
}
