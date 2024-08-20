<?php

namespace App\Tests\Card;

use App\Card\CardGraphic;
use PHPUnit\Framework\TestCase;
use OutOfBoundsException;

class CardGraphicTest extends TestCase
{
    public function testCardGraphicConstructor(): void
    {
        $cardGraphic = new CardGraphic(1);
        $this->assertInstanceOf(CardGraphic::class, $cardGraphic);
        $this->assertEquals(1, $cardGraphic->getValue());
    }

    public function testGetAsStringHeartsAce(): void
    {
        // Ace of Hearts (value 1)
        $cardGraphic = new CardGraphic(1);
        $this->assertEquals('img/carddeck/hearts_ace.png', $cardGraphic->getAsString());
    }

    public function testGetAsStringDiamondsQueen(): void
    {
        // Queen of Diamonds (value 25)
        $cardGraphic = new CardGraphic(25);
        $this->assertEquals('img/carddeck/diamonds_queen.png', $cardGraphic->getAsString());
    }

    public function testGetAsStringSpadesKing(): void
    {
        // King of Spades (value 39)
        $cardGraphic = new CardGraphic(39);
        $this->assertEquals('img/carddeck/spades_king.png', $cardGraphic->getAsString());
    }

    public function testGetAsStringOutOfBounds(): void
    {
        // Test with a card value outside the expected range (e.g., 53)
        $this->expectException(OutOfBoundsException::class); // Expect an out-of-bounds exception
        $cardGraphic = new CardGraphic(53);
        $cardGraphic->getAsString();
    }
}
