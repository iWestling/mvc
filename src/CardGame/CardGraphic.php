<?php

namespace App\CardGame;

/**
 * Represents a graphical playing card with an image path
 */
class CardGraphic extends Card
{
    /**
    * @var string The image path of the card
    */
    private string $imagePath;

    /**
     * @var string The image path of an unturned card
     */
    private string $unturned = 'img/carddeck/unturned.png';

    /**
     * Creates a new CardGraphic instance with the given value and suit
     *
     * @param int $value The value of the card
     * @param string $suit The suit of the card
     */
    public function __construct(int $value, string $suit)
    {
        parent::__construct($value, $suit);
        $this->setImagePath($value, $suit);
    }

    private function setImagePath(int $value, string $suit): void
    {
        $valueName = $this->getValueName($value);
        $this->imagePath = sprintf('img/carddeck/%s_%s.png', $suit, $valueName);
    }

    private function getValueName(int $value): string
    {
        switch ($value) {
            case 1:
                return 'ace';
            case 11:
                return 'jack';
            case 12:
                return 'queen';
            case 13:
                return 'king';
            default:
                return (string)$value;
        }
    }

    public function getAsString(): string
    {
        return $this->imagePath;
    }

    public function getUnturned(): string
    {
        return $this->unturned;
    }

}
