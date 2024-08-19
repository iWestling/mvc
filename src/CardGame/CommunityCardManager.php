<?php

namespace App\CardGame;

class CommunityCardManager
{
    /** @var CardGraphic[] */
    private array $communityCards = [];

    private Deck $deck;

    public function __construct(Deck $deck)
    {
        $this->deck = $deck;
    }

    public function dealCommunityCards(int $number): void
    {
        for ($i = 0; $i < $number; $i++) {
            $card = $this->deck->drawCard();
            if ($card instanceof CardGraphic) {
                $this->communityCards[] = $card;
            }
        }
    }

    /**
     * @return CardGraphic[]
     */
    public function getCommunityCards(): array
    {
        return $this->communityCards;
    }

    public function resetCommunityCards(): void
    {
        $this->communityCards = [];
    }

    /**
     * Deal initial cards to each player.
     *
     * @param Player[] $players Array of Player objects
     */
    public function dealInitialCards(array $players): void
    {
        foreach ($players as $player) {
            $card1 = $this->deck->drawCard();
            $card2 = $this->deck->drawCard();

            if ($card1 instanceof CardGraphic) {
                $player->addCardToHand($card1);
            }
            if ($card2 instanceof CardGraphic) {
                $player->addCardToHand($card2);
            }
        }
    }
}
