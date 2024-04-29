<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class CardHandTest extends TestCase
{
    public function testAddCardAndGetCards(): void
    {
        $card1 = new CardGraphic(5, 'hearts');
        $card2 = new CardGraphic(10, 'spades');

        $hand = new CardHand();
        $hand->addCard($card1);
        $hand->addCard($card2);

        $this->assertCount(2, $hand->getCards());
    }

    public function testCalculateTotal(): void
    {
        $card1 = new CardGraphic(5, 'hearts');
        $card2 = new CardGraphic(10, 'spades');

        $hand = new CardHand();
        $hand->addCard($card1);
        $hand->addCard($card2);

        $totals = $hand->calculateTotal();

        // Assuming no aces present, total should be sum of card values
        $this->assertEquals(15, $totals['low']);
        $this->assertEquals(15, $totals['high']);
    }

    public function testCalculateTotalWithAce(): void
    {
        $card1 = new CardGraphic(5, 'hearts');
        $card2 = new CardGraphic(1, 'spades'); // Ace

        $hand = new CardHand();
        $hand->addCard($card1);
        $hand->addCard($card2);

        $totals = $hand->calculateTotal();

        // Ace can be counted as 1 or 11
        $this->assertEquals(6, $totals['low']);
        $this->assertEquals(16, $totals['high']);
    }

    public function testCalculateTotalWithAceAsEleven(): void
    {
        $cardHand = new CardHand();
        $card1 = new CardGraphic(1, 'hearts'); // Ace
        $card2 = new CardGraphic(11, 'diamonds'); // Jack
        $cardHand->addCard($card1);
        $cardHand->addCard($card2);

        $total = $cardHand->calculateTotal();
        $this->assertEquals(21, $total['high']);
        $this->assertEquals(11, $total['low']);
    }

    public function testCalculateTotalWithFaceCards(): void
    {
        $cardHand = new CardHand();
        $card1 = new CardGraphic(11, 'hearts'); // Jack
        $card2 = new CardGraphic(12, 'diamonds'); // Queen
        $cardHand->addCard($card1);
        $cardHand->addCard($card2);

        $total = $cardHand->calculateTotal();
        $this->assertEquals(20, $total['high']);
        $this->assertEquals(20, $total['low']);
    }
    public function testCalculateTotalDealer(): void
    {
        // Create a card with a value of 5
        $card1 = new CardGraphic(5, 'hearts');

        // Add the card to the hand
        $hand = new CardHand();
        $hand->addCard($card1);

        // Calculate the total
        $total = $hand->calculateTotalDealer();

        // Assert the high value (assuming the card's value is 5)
        $this->assertEquals(5, $total['high']);
    }

    public function testCalculateTotalWithAceHighAdjustment(): void
    {
        $card1 = new CardGraphic(1, 'hearts'); // Ace
        $card2 = new CardGraphic(1, 'spades'); // Ace
        $card3 = new CardGraphic(5, 'diamonds');

        $hand = new CardHand();
        $hand->addCard($card1);
        $hand->addCard($card2);
        $hand->addCard($card3);

        $totals = $hand->calculateTotal();

        // Assuming two Aces and one 5, total high score should be 16
        $this->assertEquals(7, $totals['low']); // 1 (Ace) + 1 (Ace) + 5 (5)
        $this->assertEquals(17, $totals['high']); // 11 (Ace) + 1 (Ace) + 5 (5)
    }

    public function testCalculateTotalWithThreeAcesAndRandomCard(): void
    {
        $ace = new CardGraphic(1, 'hearts'); // Ace
        $card = new CardGraphic(5, 'diamonds'); // Random card

        $hand = new CardHand();
        // Add three aces to the hand
        $hand->addCard($ace);
        $hand->addCard($ace);
        $hand->addCard($ace);
        // Add a random card to the hand
        $hand->addCard($card);

        // Calculate the total score
        $totals = $hand->calculateTotal();

        // Assert that the high score is adjusted to stay within 21
        $this->assertEquals(8, $totals['low']); // Sum of card values (3 Aces + a random card, here 5)
        $this->assertEquals(18, $totals['high']); // Sum of card values with one Ace counted as 11 (11 + 1 + 1 + 5)
    }

    public function testCalculateTotalDealerWithAce(): void
    {
        $card1 = new CardGraphic(1, 'hearts'); // Ace
        $hand = new CardHand();
        $hand->addCard($card1);
        $total = $hand->calculateTotalDealer();

        // Assert the high value with Ace counted as 11
        $this->assertEquals(11, $total['high']);
    }

    public function testCalculateTotalDealerWithFaceCard(): void
    {
        $card1 = new CardGraphic(11, 'hearts'); // Jack
        $hand = new CardHand();
        $hand->addCard($card1);
        $total = $hand->calculateTotalDealer();

        // Assert the high value with face card counted as 10
        $this->assertEquals(10, $total['high']);
    }


}
