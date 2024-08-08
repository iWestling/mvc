<?php

namespace App\CardGame;

class HandEvaluator
{
    public static function getBestHand(array $cards): array
    {
        $bestHand = ['rank' => '', 'values' => []]; // Initialize with a valid structure

        // Generate all possible 5-card combinations
        $combinations = self::combinations($cards, 5);

        // Evaluate each combination and determine the best hand
        foreach ($combinations as $combination) {
            $rank = self::evaluateHand($combination);
            if (empty($bestHand['rank']) || self::compareHands(['rank' => $rank['rank'], 'values' => $rank['values']], $bestHand) > 0) {
                $bestHand = ['hand' => $combination, 'rank' => $rank['rank'], 'values' => $rank['values']];
            }
        }

        return $bestHand;
    }

    private static function combinations(array $cards, int $k): array
    {
        $results = [];
        $n = count($cards);
        self::combinationsHelper($cards, $n, $k, 0, [], $results);
        return $results;
    }

    private static function combinationsHelper(array $cards, int $n, int $k, int $index, array $current, array &$results)
    {
        if ($k == 0) {
            $results[] = $current;
            return;
        }

        for ($i = $index; $i <= $n - $k; $i++) {
            $current[] = $cards[$i];
            self::combinationsHelper($cards, $n, $k - 1, $i + 1, $current, $results);
            array_pop($current);
        }
    }

    private static function evaluateHand(array $hand): array
    {
        // Sort hand by card value
        usort($hand, fn($a, $b) => $a->getValue() - $b->getValue());

        if (self::isRoyalFlush($hand)) {
            return ['rank' => 'Royal Flush', 'values' => [14, 13, 12, 11, 10]];
        }
        if (self::isStraightFlush($hand)) {
            return ['rank' => 'Straight Flush', 'values' => array_map(fn($card) => $card->getValue(), $hand)];
        }
        if ($fourOfAKind = self::isFourOfAKind($hand)) {
            return ['rank' => 'Four of a Kind', 'values' => $fourOfAKind];
        }
        if ($fullHouse = self::isFullHouse($hand)) {
            return ['rank' => 'Full House', 'values' => $fullHouse];
        }
        if (self::isFlush($hand)) {
            return ['rank' => 'Flush', 'values' => array_map(fn($card) => $card->getValue(), $hand)];
        }
        if (self::isStraight($hand)) {
            return ['rank' => 'Straight', 'values' => array_map(fn($card) => $card->getValue(), $hand)];
        }
        if ($threeOfAKind = self::isThreeOfAKind($hand)) {
            return ['rank' => 'Three of a Kind', 'values' => $threeOfAKind];
        }
        if ($twoPair = self::isTwoPair($hand)) {
            return ['rank' => 'Two Pair', 'values' => $twoPair];
        }
        if ($onePair = self::isOnePair($hand)) {
            return ['rank' => 'One Pair', 'values' => $onePair];
        }

        return ['rank' => 'High Card', 'values' => array_map(fn($card) => $card->getValue(), $hand)];
    }

    private static function isRoyalFlush(array $hand): bool
    {
        return self::isStraightFlush($hand) && $hand[0]->getValue() == 10;
    }

    private static function isStraightFlush(array $hand): bool
    {
        return self::isFlush($hand) && self::isStraight($hand);
    }

    private static function isFourOfAKind(array $hand): ?array
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        foreach ($counts as $value => $count) {
            if ($count === 4) {
                return [$value, array_values(array_diff($values, [$value]))[0]];
            }
        }

        return null;
    }

    private static function isFullHouse(array $hand): ?array
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        $threeOfAKind = null;
        $pair = null;

        foreach ($counts as $value => $count) {
            if ($count === 3) {
                $threeOfAKind = $value;
            } elseif ($count === 2) {
                $pair = $value;
            }
        }

        if ($threeOfAKind && $pair) {
            return [$threeOfAKind, $pair];
        }

        return null;
    }

    private static function isFlush(array $hand): bool
    {
        $suits = array_map(fn($card) => $card->getSuit(), $hand);
        return count(array_unique($suits)) === 1;
    }

    private static function isStraight(array $hand): bool
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        sort($values);

        for ($i = 0; $i < count($values) - 1; $i++) {
            if ($values[$i + 1] !== $values[$i] + 1) {
                return false;
            }
        }

        return true;
    }

    private static function isThreeOfAKind(array $hand): ?array
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        foreach ($counts as $value => $count) {
            if ($count === 3) {
                $remainingValues = array_values(array_diff($values, [$value]));
                return [$value, $remainingValues[0], $remainingValues[1]];
            }
        }

        return null;
    }

    private static function isTwoPair(array $hand): ?array
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        $pairs = [];

        foreach ($counts as $value => $count) {
            if ($count === 2) {
                $pairs[] = $value;
            }
        }

        if (count($pairs) === 2) {
            $remainingValue = array_values(array_diff($values, $pairs))[0];
            return [$pairs[0], $pairs[1], $remainingValue];
        }

        return null;
    }

    private static function isOnePair(array $hand): ?array
    {
        $values = array_map(fn($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        foreach ($counts as $value => $count) {
            if ($count === 2) {
                $remainingValues = array_values(array_diff($values, [$value]));
                return [$value, $remainingValues[0], $remainingValues[1], $remainingValues[2]];
            }
        }

        return null;
    }

    public static function compareHands(array $hand1, array $hand2): int
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

        if (!isset($hand1['rank'], $hand2['rank'])) {
            throw new \InvalidArgumentException("Invalid hand structure: 'rank' key is missing.");
        }

        $rank1 = $rankings[$hand1['rank']];
        $rank2 = $rankings[$hand2['rank']];

        if ($rank1 > $rank2) {
            return 1;
        } elseif ($rank1 < $rank2) {
            return -1;
        }

        // If ranks are the same, compare the values
        for ($i = 0; $i < count($hand1['values']); $i++) {
            if ($hand1['values'][$i] > $hand2['values'][$i]) {
                return 1;
            } elseif ($hand1['values'][$i] < $hand2['values'][$i]) {
                return -1;
            }
        }

        return 0;
    }
}
