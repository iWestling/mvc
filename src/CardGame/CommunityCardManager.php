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
}
