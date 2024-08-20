<?php

namespace App\Tests\Card;

use App\Card\Card;
use App\Card\CardHand;
use PHPUnit\Framework\TestCase;

class CardHandTest extends TestCase
{
    public function testAddCard(): void
    {
        $hand = new CardHand();
        $card = new Card(10);

        $hand->addCard($card);
        $this->assertCount(1, $hand->getHand());
        $this->assertSame($card, $hand->getHand()[0]);
    }

    public function testGetHand(): void
    {
        $hand = new CardHand();
        $card1 = new Card(5);
        $card2 = new Card(10);

        $hand->addCard($card1);
        $hand->addCard($card2);

        $this->assertCount(2, $hand->getHand());
        $this->assertSame([$card1, $card2], $hand->getHand());
    }

    public function testDrawMultipleCards(): void
    {
        $deck = [new Card(1), new Card(2), new Card(3), new Card(4)];
        $hand = new CardHand();

        $drawnCards = $hand->drawMultipleCards($deck, 3);

        $this->assertCount(3, $drawnCards);
        $this->assertCount(1, $deck); // 1 card should be left in the deck
        $this->assertCount(3, $hand->getHand()); // The hand should have 3 cards
    }

    public function testDrawMultipleCardsWhenDeckIsEmpty(): void
    {
        $deck = [];
        $hand = new CardHand();

        $drawnCards = $hand->drawMultipleCards($deck, 3);

        $this->assertCount(0, $drawnCards); // No cards drawn
        $this->assertCount(0, $hand->getHand()); // Hand should be empty
    }

    public function testDealCardsToPlayers(): void
    {
        $deck = [
            new Card(1), new Card(2), new Card(3), new Card(4),
            new Card(5), new Card(6)
        ];

        $cardHandInstance = new CardHand(); // Create an instance of CardHand if needed
        $playerHands = $cardHandInstance->dealCardsToPlayers($deck, 2, 3);

        // Ensure each player has 3 cards
        $this->assertCount(2, $playerHands);
        $this->assertCount(3, $playerHands[0]->getHand());
        $this->assertCount(3, $playerHands[1]->getHand());

        // Ensure the deck is now empty
        $this->assertCount(0, $deck);
    }

    public function testDealCardsToPlayersWithNotEnoughCards(): void
    {
        $deck = [
            new Card(1), new Card(2), new Card(3), new Card(4)
        ];

        $cardHandInstance = new CardHand(); // Create an instance of CardHand if needed
        $playerHands = $cardHandInstance->dealCardsToPlayers($deck, 2, 3);

        // First player should have 2 cards, second player should have 2 cards
        $this->assertCount(2, $playerHands);
        $this->assertCount(2, $playerHands[0]->getHand());
        $this->assertCount(2, $playerHands[1]->getHand());

        // Ensure the deck is now empty
        $this->assertCount(0, $deck);
    }
}
