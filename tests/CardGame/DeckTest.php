<?php

namespace App\Tests\CardGame;

use App\CardGame\Deck;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    public function testDeckIsInitializedWith52Cards(): void
    {
        $deck = new Deck();
        $cards = $deck->getCards();

        // The deck should have 52 cards
        $this->assertCount(52, $cards);

        foreach ($cards as $card) {
            $this->assertInstanceOf(CardGraphic::class, $card);
        }
    }

    public function testDeckContainsCorrectCardCombination(): void
    {
        $deck = new Deck();
        $cards = $deck->getCards();

        $expectedCards = [];
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];

        foreach ($suits as $suit) {
            for ($value = 1; $value <= 13; $value++) {
                $expectedCards[] = new CardGraphic($value, $suit);
            }
        }

        // Check if all expected cards are in the deck
        foreach ($expectedCards as $expectedCard) {
            $this->assertContainsEquals($expectedCard, $cards);
        }
    }

    public function testDrawCardReducesDeckSize(): void
    {
        $deck = new Deck();
        $initialCount = count($deck->getCards());

        $drawnCard = $deck->drawCard();

        // Ensure a card is drawn
        $this->assertInstanceOf(CardGraphic::class, $drawnCard);

        // The deck should now have one less card
        $this->assertCount($initialCount - 1, $deck->getCards());
    }

    public function testDrawAllCardsAndDeckIsEmpty(): void
    {
        $deck = new Deck();

        // Draw all 52 cards
        for ($i = 0; $i < 52; $i++) {
            $this->assertInstanceOf(CardGraphic::class, $deck->drawCard());
        }

        // After drawing all cards, the deck should be empty
        $this->assertNull($deck->drawCard());
        $this->assertEmpty($deck->getCards());
    }

    public function testShuffleRandomizesDeckOrder(): void
    {
        $deck1 = new Deck();
        $deck2 = new Deck();

        $cards1 = $deck1->getCards();
        $cards2 = $deck2->getCards();

        $this->assertNotEquals($cards1, $cards2, "Two shuffled decks should not be in the same order.");
    }
}
