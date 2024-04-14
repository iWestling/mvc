<?php

namespace App\CardGame;

class CardGraphic extends Card
{

    private string $imagePath;

    private string $unturned = 'img/carddeck/unturned.png';

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