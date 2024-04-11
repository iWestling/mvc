<?php

namespace App\Card;

class Card
{
    protected $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getAsString(): string
    {
        return "[{$this->value}]";
    }

    public function getForAPI(): string
    {
        $suits = ['♥', '♦', '♣', '♠'];
        $ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        $suitIndex = (($this->value - 1) % 4);
        $rankIndex = (($this->value - 1) / 4);

        return "[{$ranks[$rankIndex]}{$suits[$suitIndex]}]";
    }
}
