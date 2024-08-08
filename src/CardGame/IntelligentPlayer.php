<?php

namespace App\CardGame;

class IntelligentPlayer
{
    public function makeDecision(Player $player, array $communityCards, int $currentBet): string
    {
        $hand = array_merge($player->getHand(), $communityCards);
        $hasHighCard = $this->hasHighCard($player);
        $hasPair = $this->hasPair($hand);

        if ($currentBet > 0 && !$hasPair && !$hasHighCard) {
            return 'fold';
        }
        
        if ($this->hasStrongHand($hand)) {
            return 'raise';
        }

        if ($currentBet > 0) {
            return 'call';
        }

        return 'check';
    }

    private function hasHighCard(Player $player): bool
    {
        foreach ($player->getHand() as $card) {
            if (in_array($card->getValue(), [1, 11, 12, 13])) {
                return true;
            }
        }
        return false;
    }

    private function hasPair(array $hand): bool
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        return count(array_unique($values)) < count($values);
    }

    private function hasStrongHand(array $hand): bool
    {
        // Implement logic to check for strong hands like flush, full house, etc.
        // For simplicity, let's assume it returns true for now.
        return false;
    }
}
