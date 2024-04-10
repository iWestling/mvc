<?php

namespace App\Card;

class CardGraphic extends Card
{
    private $representation = [
        'img/carddeck/heart_ace.png',
        'img/carddeck/heart_two.png',
        'img/carddeck/heart_three.png',
        'img/carddeck/heart_four.png',
        'img/carddeck/heart_five.png',
        'img/carddeck/heart_six.png',
        'img/carddeck/heart_seven.png',
        'img/carddeck/heart_eight.png',
        'img/carddeck/heart_nine.png',
        'img/carddeck/heart_ten.png',
        'img/carddeck/heart_jack.png',
        'img/carddeck/heart_queen.png',
        'img/carddeck/heart_king.png',
        'img/carddeck/diamond_ace.png',
        'img/carddeck/diamond_two.png',
        'img/carddeck/diamond_three.png',
        'img/carddeck/diamond_four.png',
        'img/carddeck/diamond_five.png',
        'img/carddeck/diamond_six.png',
        'img/carddeck/diamond_seven.png',
        'img/carddeck/diamond_eight.png',
        'img/carddeck/diamond_nine.png',
        'img/carddeck/diamond_ten.png',
        'img/carddeck/diamond_jack.png',
        'img/carddeck/diamond_queen.png',
        'img/carddeck/diamond_king.png',
        'img/carddeck/spades_ace.png',
        'img/carddeck/spades_two.png',
        'img/carddeck/spades_three.png',
        'img/carddeck/spades_four.png',
        'img/carddeck/spades_five.png',
        'img/carddeck/spades_six.png',
        'img/carddeck/spades_seven.png',
        'img/carddeck/spades_eight.png',
        'img/carddeck/spades_nine.png',
        'img/carddeck/spades_ten.png',
        'img/carddeck/spades_jack.png',
        'img/carddeck/spades_queen.png',
        'img/carddeck/spades_king.png',
        'img/carddeck/clubs_ace.png',
        'img/carddeck/clubs_two.png',
        'img/carddeck/clubs_three.png',
        'img/carddeck/clubs_four.png',
        'img/carddeck/clubs_five.png',
        'img/carddeck/clubs_six.png',
        'img/carddeck/clubs_seven.png',
        'img/carddeck/clubs_eight.png',
        'img/carddeck/clubs_nine.png',
        'img/carddeck/clubs_ten.png',
        'img/carddeck/clubs_jack.png',
        'img/carddeck/clubs_queen.png',
        'img/carddeck/clubs_king.png'
    ];

    private $apirepresentation = [
        '[A♥]',
        '[2♥]',
        '[3♥]',
        '[4♥]',
        '[5♥]',
        '[6♥]',
        '[7♥]',
        '[8♥]',
        '[9♥]',
        '[10♥]',
        '[J♥]',
        '[Q♥]',
        '[K♥]',
        '[A♦]',
        '[2♦]',
        '[3♦]',
        '[4♦]',
        '[5♦]',
        '[6♦]',
        '[7♦]',
        '[8♦]',
        '[9♦]',
        '[10♦]',
        '[J♦]',
        '[Q♦]',
        '[K♦]',
        '[A♣]',
        '[2♣]',
        '[3♣]',
        '[4♣]',
        '[5♣]',
        '[6♣]',
        '[7♣]',
        '[8♣]',
        '[9♣]',
        '[10♣]',
        '[J♣]',
        '[Q♣]',
        '[K♣]',
        '[A♠]',
        '[2♠]',
        '[3♠]',
        '[4♠]',
        '[5♠]',
        '[6♠]',
        '[7♠]',
        '[8♠]',
        '[9♠]',
        '[10♠]',
        '[J♠]',
        '[Q♠]',
        '[K♠]'
    ];
    public function getAsString(): string
    {
        return $this->representation[$this->getValue() - 1];
    }
    public function getCardForAPI(): string
    {
        return $this->apirepresentation[$this->getValue() - 1];
    }

}
