<?php

namespace App\CardGame;

class Card
{
    protected int $value;
    protected string $suit;

    public function __construct(int $value, string $suit)
    {
        $this->value = $value;
        $this->suit = $suit;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getCardName(): string
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

    public function getSuit(): string
    {
        return $this->suit;
    }

}
