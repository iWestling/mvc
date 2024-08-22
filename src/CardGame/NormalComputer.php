<?php

namespace App\CardGame;

class NormalComputer
{
    /**
     * @param CardGraphic[] $communityCards
     * @return string
     */
    public function makeDecision(Player $player, array $communityCards, int $currentBet): string
    {
        $hand = array_merge($player->getHand(), $communityCards);
        $hasHighCard = $this->hasHighCard($player);
        $hasPair = $this->hasPair($hand);

        if ($currentBet > 0 && !$hasPair && !$hasHighCard) {
            return 'fold';
        }

        if ($currentBet > 0) {
            return 'call';
        }

        return 'check';
    }

    private function hasHighCard(Player $player): bool
    {
        foreach ($player->getHand() as $card) {
            if (in_array($card->getValue(), [1, 11, 12, 13, 14])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param CardGraphic[] $hand
     * @return bool
     */
    private function hasPair(array $hand): bool
    {
        $values = array_map(fn ($card) => $card->getValue(), $hand);
        return count(array_unique($values)) < count($values);
    }
}
