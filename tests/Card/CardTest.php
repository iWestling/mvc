<?php

namespace App\Tests\Card;

use App\Card\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    public function testCardConstructor(): void
    {
        $card = new Card(5);
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals(5, $card->getValue());
    }

    public function testGetValue(): void
    {
        $card = new Card(10);
        $this->assertEquals(10, $card->getValue());
    }

    public function testGetAsString(): void
    {
        $card = new Card(12);
        $this->assertEquals('[12]', $card->getAsString());
    }

    public function testGetForAPIWithHeartsAce(): void
    {
        // Ace of Hearts (value 1)
        $card = new Card(1);
        $this->assertEquals('[A♥]', $card->getForAPI());
    }

    public function testGetForAPIWithSpadesKing(): void
    {
        // King of Spades (value 52)
        $card = new Card(52);
        $this->assertEquals('[K♠]', $card->getForAPI());
    }
}
