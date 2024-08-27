<?php

namespace App\CardGame;

use App\CardGame\HandEvaluator;

class IntelligentComputer
{
    private HandEvaluator $handEvaluator;

    public function __construct()
    {
        $this->handEvaluator = new HandEvaluator();
    }

    /**
     * @param CardGraphic[] $communityCards
     * @return string
     */
    public function makeDecision(Player $player, array $communityCards, int $currentBet): string
    {
        $hand = array_merge($player->getHand(), $communityCards);
        $bestHand = $this->handEvaluator->getBestHand($hand);
        $rankValue = $this->handEvaluator->getRankValue($bestHand);
        $playerChips = $player->getChips();
        $hasHighCard = $this->hasHighCard($player);

        if ($this->shouldGoAllIn($rankValue)) {
            return 'all-in';
        }

        if ($this->shouldFoldForAllIn($currentBet, $playerChips, $rankValue)) {
            return 'fold';
        }

        if ($this->shouldCallForAllIn($currentBet, $playerChips, $rankValue)) {
            return 'call';
        }

        if ($this->shouldRaise($rankValue)) {
            return 'raise';
        }

        if ($this->shouldCall($rankValue, $currentBet, $playerChips)) {
            return 'call';
        }

        if ($this->shouldFold($currentBet, $hasHighCard, $rankValue)) {
            return 'fold';
        }

        if ($currentBet > 0) {
            return 'call';
        }

        return 'check';
    }

    private function shouldGoAllIn(int $rankValue): bool
    {
        return $rankValue === 9 || $rankValue === 10; // Straight Flush or Royal Flush
    }

    private function shouldFoldForAllIn(int $currentBet, int $playerChips, int $rankValue): bool
    {
        return $currentBet >= $playerChips && $rankValue < 4;
    }

    private function shouldCallForAllIn(int $currentBet, int $playerChips, int $rankValue): bool
    {
        return $currentBet >= $playerChips && $rankValue >= 4;
    }

    private function shouldRaise(int $rankValue): bool
    {
        return $rankValue > 4; // Raise if rank value is higher than 4
    }

    private function shouldCall(int $rankValue, int $currentBet, int $playerChips): bool
    {
        return $rankValue >= 2 && $currentBet <= ($playerChips / 20);
    }

    private function shouldFold(int $currentBet, bool $hasHighCard, int $rankValue): bool
    {
        return $currentBet > 0 && !$hasHighCard && $rankValue < 2;
    }

    private function hasHighCard(Player $player): bool
    {
        foreach ($player->getHand() as $card) {
            if (in_array($card->getValue(), [1, 11, 12, 13, 14])) { // Ace, Jack, Queen, King
                return true;
            }
        }
        return false;
    }
}
