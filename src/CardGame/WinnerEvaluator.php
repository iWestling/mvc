<?php

namespace App\CardGame;

class WinnerEvaluator
{
    private HandEvaluator $handEvaluator;

    public function __construct(HandEvaluator $handEvaluator)
    {
        $this->handEvaluator = $handEvaluator;
    }

    /**
     * Determine the winner(s) from the remaining players
     *
     * @param Player[] $remainingPlayers
     * @param CardGraphic[] $communityCards
     * @return Player[]
     */
    public function determineWinners(array $remainingPlayers, array $communityCards): array
    {
        $bestHand = ['rank' => '', 'values' => []];
        $winners = [];

        foreach ($remainingPlayers as $player) {
            $hand = array_merge($player->getHand(), $communityCards);
            $bestPlayerHand = $this->handEvaluator->getBestHand($hand);

            if (empty($bestHand['rank']) || $this->handEvaluator->compareHands($bestPlayerHand, $bestHand) > 0) {
                $bestHand = $bestPlayerHand;
                $winners = [$player];
            } elseif ($this->handEvaluator->compareHands($bestPlayerHand, $bestHand) === 0) {
                $winners[] = $player;
            }
        }

        return $winners;
    }

    public function getHandEvaluator(): HandEvaluator
    {
        return $this->handEvaluator;
    }
}
