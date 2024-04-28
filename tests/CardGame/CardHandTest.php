<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class CardHandTest extends TestCase
{
    public function testAddCard()
    {
        $cardHand = new CardHand();
        $card = new CardGraphic(5, 'hearts');
        $cardHand->addCard($card);

        $this->assertCount(1, $cardHand->getCards());
    }
    public function testAddCardAndGetCards()
    {
        $card1 = new CardGraphic(5, 'hearts');
        $card2 = new CardGraphic(10, 'spades');

        $hand = new CardHand();
        $hand->addCard($card1);
        $hand->addCard($card2);

        $this->assertCount(2, $hand->getCards());
    }

    public function testCalculateTotal()
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

    public function testCalculateTotalWithAce()
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

    public function testCalculateTotalWithAceAsEleven()
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

    public function testCalculateTotalWithFaceCards()
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
    public function testCalculateTotalDealer()
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

    public function testCalculateTotalWithAceHighAdjustment()
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
        $this->assertEquals(17, $totals['high']); // 11 (Ace) + 5 (5)
    }



    public function testCalculateTotalDealerWithAce()
    {
        $card1 = new CardGraphic(1, 'hearts'); // Ace
        $hand = new CardHand();
        $hand->addCard($card1);
        $total = $hand->calculateTotalDealer();

        // Assert the high value with Ace counted as 11
        $this->assertEquals(11, $total['high']);
    }

    public function testCalculateTotalDealerWithFaceCard()
    {
        $card1 = new CardGraphic(11, 'hearts'); // Jack
        $hand = new CardHand();
        $hand->addCard($card1);
        $total = $hand->calculateTotalDealer();

        // Assert the high value with face card counted as 10
        $this->assertEquals(10, $total['high']);
    }


}
