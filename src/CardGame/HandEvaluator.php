<?php

namespace App\CardGame;

use App\CardGame\HandRankingEvaluator;

class HandEvaluator
{
    private HandRankingEvaluator $rankingEvaluator;

    public function __construct(?HandRankingEvaluator $rankingEvaluator = null)
    {
        $this->rankingEvaluator = $rankingEvaluator ?? new HandRankingEvaluator();
    }

    /**
     * @param CardGraphic[] $cards
     * @return array<string, mixed>
     */
    public function getBestHand(array $cards): array
    {
        $bestHand = ['rank' => '', 'values' => []];
        $combinations = $this->combinations($cards, 5);

        foreach ($combinations as $combination) {
            $rank = $this->rankingEvaluator->evaluateHand($combination);
            if ($this->isBetterHand($rank, $bestHand)) {
                $bestHand = ['hand' => $combination, 'rank' => $rank['rank'], 'values' => $rank['values']];
            }
        }

        return $bestHand;
    }

    /**
     * @param array<string, mixed> $rank
     * @param array<string, mixed> $bestHand
     * @return bool
     */
    private function isBetterHand(array $rank, array $bestHand): bool
    {
        return $this->compareHands($rank, $bestHand) > 0;
    }

    /**
     * @param CardGraphic[] $cards
     * @return CardGraphic[][]
     */
    public function combinations(array $cards, int $kcomb): array
    {
        $results = [];
        $ncomb = count($cards);
        $this->combinationsHelper($cards, $ncomb, $kcomb, 0, [], $results);
        return $results;
    }

    /**
     * @param CardGraphic[] $cards
     * @param CardGraphic[] $current
     * @param CardGraphic[][] $results
     */
    private function combinationsHelper(array $cards, int $ncomb, int $kcomb, int $index, array $current, array &$results): void
    {
        if ($kcomb == 0) {
            $results[] = $current;
            return;
        }

        for ($i = $index; $i <= $ncomb - $kcomb; $i++) {
            $current[] = $cards[$i];
            $this->combinationsHelper($cards, $ncomb, $kcomb - 1, $i + 1, $current, $results);
            array_pop($current);
        }
    }

    /**
     * @param array<string, mixed> $hand1
     * @param array<string, mixed> $hand2
     * @return int
     */
    public function compareHands(array $hand1, array $hand2): int
    {
        if (!$this->areHandsValid($hand1, $hand2)) {
            return -1;
        }

        $rank1 = $this->getRankValue($hand1);
        $rank2 = $this->getRankValue($hand2);

        // Compare ranks
        if ($rank1 > $rank2) {
            return 1;
        } elseif ($rank1 < $rank2) {
            return -1;
        }

        // Ensure 'values' is an array before comparing
        $values1 = $hand1['values'] ?? [];
        $values2 = $hand2['values'] ?? [];

        if (!is_array($values1) || !is_array($values2)) {
            return 0; // Treat invalid values as equal
        }

        // Compare the hand values (kickers)
        return $this->compareHandValues($values1, $values2);
    }



    /**
     * Check if the hands are valid
     *
     * @param array<string, mixed> $hand1
     * @param array<string, mixed> $hand2
     * @return bool
     */
    private function areHandsValid(array $hand1, array $hand2): bool
    {
        return isset($hand1['rank'], $hand2['rank']);
    }

    /**
     * Get the rank value of the hand
     *
     * @param array<string, mixed> $hand
     * @return int
     */
    private function getRankValue(array $hand): int
    {
        $rankings = [
            'High Card' => 1,
            'One Pair' => 2,
            'Two Pair' => 3,
            'Three of a Kind' => 4,
            'Straight' => 5,
            'Flush' => 6,
            'Full House' => 7,
            'Four of a Kind' => 8,
            'Straight Flush' => 9,
            'Royal Flush' => 10,
        ];

        return $rankings[$hand['rank']] ?? 0;
    }

    /**
     * Compare the values of the hands
     *
     * @param array<int> $values1
     * @param array<int> $values2
     * @return int
     */
    private function compareHandValues(array $values1, array $values2): int
    {
        for ($i = 0, $valueCount = count($values1); $i < $valueCount; $i++) {
            if (isset($values1[$i], $values2[$i])) {
                if ($values1[$i] > $values2[$i]) {
                    return 1;
                } elseif ($values1[$i] < $values2[$i]) {
                    return -1;
                }
            }
        }

        return 0; // Return 0 if hands are identical
    }

}
