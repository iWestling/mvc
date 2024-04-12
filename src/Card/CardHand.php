<?php

namespace App\Card;

class CardHand
{
    /**
     * @var Card[]
     */
    private array $hand = [];

    public function addCard(Card $card): void
    {
        if ($card !== null) {
            $this->hand[] = $card;
        }
    }

    /**
     * @return Card[]
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    /**
     * @param array<Card> $deck
     * @param int $number
     * @return Card[]
     */
    public function drawMultipleCards(array &$deck, int $number): array
    {
        $drawnCards = [];
        $remainingCards = count($deck);
        for ($i = 0; $i < $number && $remainingCards > 0; $i++) {
            $drawnCard = array_shift($deck);
            if ($drawnCard !== null) {
                $this->addCard($drawnCard);
                $drawnCards[] = $drawnCard;
            }
            $remainingCards--;
        }
        return array_filter($drawnCards); // removes null values
    }

    /**
     * @param array<Card> $deck
     * @param int $players
     * @param int $cards
     * @return CardHand[]
     */
    public static function dealCardsToPlayers(array &$deck, int $players, int $cards): array
    {
        $playerHands = [];
        for ($i = 1; $i <= $players; $i++) {
            $playerHands[] = new self();
        }
        for ($i = 0; $i < $cards; $i++) {
            foreach ($playerHands as $hand) {
                if (empty($deck)) {
                    break;
                }
                $drawnCard = array_shift($deck);
                $hand->addCard($drawnCard);
            }
        }
        return $playerHands;
    }
}
