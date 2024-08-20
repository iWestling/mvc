<?php

namespace App\Tests\Card;

use App\Card\DeckOfCards;
use App\Card\Card;
use PHPUnit\Framework\TestCase;

class DeckOfCardsTest extends TestCase
{
    public function testDeckConstructor(): void
    {
        // Instantiate the deck
        $deck = new DeckOfCards();

        // Check that it's an instance of DeckOfCards
        $this->assertInstanceOf(DeckOfCards::class, $deck);

        // Check that the deck contains 52 cards
        $this->assertCount(52, $deck->getCards());
    }

    public function testDeckContainsUniqueCards(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->getCards();

        // Collect all card values
        $cardValues = array_map(fn (Card $card) => $card->getValue(), $cards);

        // Ensure all card values are unique
        $this->assertCount(52, array_unique($cardValues));
    }

    public function testDeckHasCorrectCardValues(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->getCards();

        // Collect all card values
        $cardValues = array_map(fn (Card $card) => $card->getValue(), $cards);

        // Ensure the deck contains all values from 1 to 52
        $expectedValues = range(1, 52);
        $this->assertEquals($expectedValues, $cardValues);
    }

    public function testGetCardsReturnsArrayOfCardObjects(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->getCards();

        // Check that each item in the array is an instance of Card
        foreach ($cards as $card) {
            $this->assertInstanceOf(Card::class, $card);
        }
    }
}
