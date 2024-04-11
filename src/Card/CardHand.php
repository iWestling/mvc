<?php

namespace App\Card;

class CardHand
{
    private $hand = [];

    public function addCard(Card $card): void
    {
        $this->hand[] = $card;
    }

    public function getHand(): array
    {
        return $this->hand;
    }

    // Draw multiple cards
    public function drawMultipleCards(array &$deck, int $number): array
    {
        $drawnCards = [];
        for ($i = 0; $i < $number; $i++) {
            if (!empty($deck)) {
                $drawnCard = array_shift($deck);
                $this->addCard($drawnCard);
                $drawnCards[] = $drawnCard;
            } else {
                break;
            }
        }
        return $drawnCards;
    }

    // Deal cards to players
    public static function dealCardsToPlayers(array &$deck, int $players, int $cards): array
    {
        $playerHands = [];
        for ($i = 0; $i < $players; $i++) {
            $playerHands[] = new self();
        }
        for ($i = 0; $i < $cards; $i++) {
            foreach ($playerHands as $hand) {
                if (!empty($deck)) {
                    $drawnCard = array_shift($deck);
                    $hand->addCard($drawnCard);
                } else {
                    break;
                }
            }
        }
        return $playerHands;
    }
}
