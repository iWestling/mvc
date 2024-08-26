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
        $hasHighCard = $this->hasHighCard($player);

        if ($currentBet > 4 && !$hasHighCard || $rankValue < 1) {
            return 'fold';
        }

        if ($rankValue === 9 || $rankValue === 10) { // 9 = Straight Flush, 10 = Royal Flush
            return 'all-in';
        }

        if ($rankValue >= 4) { // 4 or higher (e.g., Three of a Kind or better)
            return 'raise'; // AI raises by 10
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

}
