<?php

namespace App\CardGame;

/**
 * Represents a hand of cards
 */
class CardHand
{
    /**
     * @var CardGraphic[] The cards in the hand
     */
    private array $cards;

    /**
     * Constructs a new CardHand instance
     */
    public function __construct()
    {
        $this->cards = [];
    }

    /**
     * Adds a card to the hand
     *
     * @param CardGraphic $card The card to add
     */
    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Gets the cards in hand
     *
     * @return CardGraphic[] The cards in the hand
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Calculates the total value of the hand
     *
     * @return array{low: int, high: int} Total value of the hand
     */
    public function calculateTotal(): array
    {
        $sumLow = 0; // Total score considering ace as 1
        $sumHigh = 0; // Total score considering ace as 11
        $aceCount = 0;

        foreach ($this->cards as $card) {
            $value = $card->getValue();
            $aceCount += $this->isAce($value) ? 1 : 0;
            $sumLow += $this->isAce($value) ? 1 : min($value, 10);
            $sumHigh += min($value, 10);
        }

        // If there are aces and the high score doesn't exceed 11, adjust high score
        while ($aceCount > 0 && $sumHigh <= 11) {
            $sumHigh += 10;
            $aceCount--;
        }

        return ['low' => $sumLow, 'high' => $sumHigh];
    }

    /**
     * Check if a card value represents an ace
     */
    private function isAce(int $value): bool
    {
        return $value === 1;
    }

    /**
     * Calculates the total value of the dealer's hand
     *
     * @return array{low: int, high: int} The total value of the dealer's hand
     */
    public function calculateTotalDealer(): array
    {
        $sumLow = 0; // Total score considering ace as 1
        $sumHigh = 0; // Total score considering ace as 11

        // Only calculate the value of first card
        if (!empty($this->cards)) {
            $firstCardValue = $this->cards[0]->getValue();

            // Handle ace value
            if ($firstCardValue === 1) {
                $sumLow += 1;
                $sumHigh += 11;
            }

            // Handle other card values
            if ($firstCardValue >= 2 && $firstCardValue <= 10) {
                $sumLow += $firstCardValue;
                $sumHigh += $firstCardValue;
            }

            // Handle face cards
            if ($firstCardValue >= 11 && $firstCardValue <= 13) {
                $sumLow += 10;
                $sumHigh += 10;
            }
        }

        return ['low' => $sumLow, 'high' => $sumHigh];
    }

    /**
     * Deals cards to the hand
     *
     * @param CardGraphic[] $shuffledDeck The deck to deal from
     * @param int $count The number of cards to deal
     * @return bool True if all cards were dealt successfully, false otherwise
     */
    public function dealCards(array &$shuffledDeck, int $count): bool
    {
        $dealSuccess = true;
        for ($i = 0; $i < $count; $i++) {
            $card = array_shift($shuffledDeck);
            if (!($card instanceof CardGraphic)) {
                $dealSuccess = false;
                break;
            }
            $this->addCard($card);
        }
        return $dealSuccess;
    }
}
