<?php

namespace App\Card;

use OutOfBoundsException;

class CardGraphic extends Card
{
    /**
     * @var string[]
     */
    private array $imagepath = [
        'img/carddeck/hearts_ace.png',
        'img/carddeck/hearts_2.png',
        'img/carddeck/hearts_3.png',
        'img/carddeck/hearts_4.png',
        'img/carddeck/hearts_5.png',
        'img/carddeck/hearts_6.png',
        'img/carddeck/hearts_7.png',
        'img/carddeck/hearts_8.png',
        'img/carddeck/hearts_9.png',
        'img/carddeck/hearts_10.png',
        'img/carddeck/hearts_jack.png',
        'img/carddeck/hearts_queen.png',
        'img/carddeck/hearts_king.png',
        'img/carddeck/diamonds_ace.png',
        'img/carddeck/diamonds_2.png',
        'img/carddeck/diamonds_3.png',
        'img/carddeck/diamonds_4.png',
        'img/carddeck/diamonds_5.png',
        'img/carddeck/diamonds_6.png',
        'img/carddeck/diamonds_7.png',
        'img/carddeck/diamonds_8.png',
        'img/carddeck/diamonds_9.png',
        'img/carddeck/diamonds_10.png',
        'img/carddeck/diamonds_jack.png',
        'img/carddeck/diamonds_queen.png',
        'img/carddeck/diamonds_king.png',
        'img/carddeck/spades_ace.png',
        'img/carddeck/spades_2.png',
        'img/carddeck/spades_3.png',
        'img/carddeck/spades_4.png',
        'img/carddeck/spades_5.png',
        'img/carddeck/spades_6.png',
        'img/carddeck/spades_7.png',
        'img/carddeck/spades_8.png',
        'img/carddeck/spades_9.png',
        'img/carddeck/spades_10.png',
        'img/carddeck/spades_jack.png',
        'img/carddeck/spades_queen.png',
        'img/carddeck/spades_king.png',
        'img/carddeck/clubs_ace.png',
        'img/carddeck/clubs_2.png',
        'img/carddeck/clubs_3.png',
        'img/carddeck/clubs_4.png',
        'img/carddeck/clubs_5.png',
        'img/carddeck/clubs_6.png',
        'img/carddeck/clubs_7.png',
        'img/carddeck/clubs_8.png',
        'img/carddeck/clubs_9.png',
        'img/carddeck/clubs_10.png',
        'img/carddeck/clubs_jack.png',
        'img/carddeck/clubs_queen.png',
        'img/carddeck/clubs_king.png'
    ];

    public function getAsString(): string
    {
        $value = $this->getValue() - 1; // Adjust index

        // Check for out-of-bounds access
        if ($value < 0 || $value >= count($this->imagepath)) {
            throw new OutOfBoundsException("Card value is out of bounds.");
        }

        return $this->imagepath[$value];
    }
}
