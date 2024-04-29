<?php

namespace App\CardGame;

/**
 * Represents a deck of cards containing CardGraphic instances
 */
class DeckOfCards
{
    /**
     * @var CardGraphic[] The deck of cards.
     */
    private array $deck;

    /**
     * Constructs a new DeckOfCards instance and generates the deck of cards
     */    public function __construct()
    {
        $this->deck = [];
        $this->generateDeck();
    }

    /**
     * Generates the deck of cards
     */
    private function generateDeck(): void
    {
        $suits = ['hearts', 'diamonds', 'spades', 'clubs'];
        foreach ($suits as $suit) {
            for ($value = 1; $value <= 13; $value++) {
                $cardGraphic = new CardGraphic($value, $suit);
                $this->deck[] = $cardGraphic;
            }
        }
    }

    /**
     * Gets the deck of cards
     *
     * @return CardGraphic[] deck of cards
     */
    public function getDeck(): array
    {
        return $this->deck;
    }
}
