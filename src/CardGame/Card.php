<?php

namespace App\CardGame;

/**
 * Represents a playing card, has value and suit
 */
class Card
{
    /**
     * @var int The value of the card
     */
    protected int $value;

    /**
     * @var string The suit of the card
     */
    protected string $suit;

    /**
     * Creates a new Card instance with the given value and suit
     *
     * @param int $value The value of the card
     * @param string $suit The suit of the card
     */
    public function __construct(int $value, string $suit)
    {
        $this->value = $value;
        $this->suit = $suit;
    }

    /**
     * Gets the value of the card
     *
     * @return int The value of the card
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Gets the name of the card
     *
     * @return string The name of the card
     */    public function getCardName(): string
    {
        switch ($this->value) {
            case 1:
                return 'ace';
            case 11:
                return 'jack';
            case 12:
                return 'queen';
            case 13:
                return 'king';
            default:
                return (string)$this->value;
        }
    }

    /**
     * Gets the suit of the card
     *
     * @return string The suit of the card
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

}
