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
        $bestHand = ['rank' => '', 'values' => [], 'kickers' => [], 'hand' => []];
        $combinations = $this->combinations($cards, 5);

        foreach ($combinations as $combination) {
            $rank = $this->rankingEvaluator->evaluateHand($combination);
            if ($this->isBetterHand($rank, $bestHand)) {
                // Extract kickers from remaining cards
                $remainingCards = array_udiff($cards, $combination, function ($cardA, $cardB) {
                    return $cardA->getValue() === $cardB->getValue() && $cardA->getSuit() === $cardB->getSuit() ? 0 : -1;
                });
                usort($remainingCards, fn ($carda, $cardb) => $cardb->getValue() - $carda->getValue()); // Sort remaining cards by value
                $kickers = array_slice(array_map(fn ($card) => $card->getValue(), $remainingCards), 0, 2); // Take top 2 kickers

                // Include the combination of cards as the hand
                $bestHand = ['hand' => $combination, 'rank' => $rank['rank'], 'values' => $rank['values'], 'kickers' => $kickers];

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

        // Compare the ranks
        $rankComparison = $this->compareRanks($hand1, $hand2);
        if ($rankComparison !== 0) {
            return $rankComparison;
        }

        // Compare the hand values
        $values1 = $this->filterToIntArray($hand1['values'] ?? []);
        $values2 = $this->filterToIntArray($hand2['values'] ?? []);
        $valueComparison = $this->compareHandValuesSafely($values1, $values2);
        if ($valueComparison !== 0) {
            return $valueComparison;
        }

        // Compare the kickers
        $kickers1 = $this->filterToIntArray($hand1['kickers'] ?? []);
        $kickers2 = $this->filterToIntArray($hand2['kickers'] ?? []);
        return $this->compareHandValuesSafely($kickers1, $kickers2);
    }

    /**
     * @param mixed $array
     * @return int[]
     */
    private function filterToIntArray($array): array
    {
        return is_array($array) ? array_filter($array, 'is_int') : [];
    }

    /**
     * @param array<string, mixed> $hand1
     * @param array<string, mixed> $hand2
     * @return int
     */
    private function compareRanks(array $hand1, array $hand2): int
    {
        $rank1 = $this->getRankValue($hand1);
        $rank2 = $this->getRankValue($hand2);

        if ($rank1 > $rank2) {
            return 1;
        } elseif ($rank1 < $rank2) {
            return -1;
        }

        return 0;
    }

    /**
     * @param int[] $values1
     * @param int[] $values2
     * @return int
     */
    private function compareHandValuesSafely(array $values1, array $values2): int
    {
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
    public function getRankValue(array $hand): int
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
        $valueCount = min(count($values1), count($values2));

        for ($i = 0; $i < $valueCount; $i++) {
            if ($values1[$i] > $values2[$i]) {
                return 1;
            } elseif ($values1[$i] < $values2[$i]) {
                return -1;
            }
        }

        // If all values are equal, the hands are considered equal
        return 0;
    }

}
