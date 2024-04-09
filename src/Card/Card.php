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

    public function getCardAsString(): string
    {
        return "[{$this->value}]";
    }
}
