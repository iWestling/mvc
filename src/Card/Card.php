<?php

namespace App\Card;

class Card
{
    protected int $value;

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
        $suits = ['♥', '♦', '♣', '♠']; // Correct order for suits
        $ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

        // Adjust the value to be zero-based for both rank and suit calculations
        $suitIndex = intdiv($this->value - 1, 13);  // Integer division to determine the suit
        $rankIndex = ($this->value - 1) % 13;  // Modulo operation to determine the rank

        return "[{$ranks[$rankIndex]}{$suits[$suitIndex]}]";
    }




}
