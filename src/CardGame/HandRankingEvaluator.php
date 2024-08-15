<?php

namespace App\CardGame;

class HandRankingEvaluator
{
    /**
     * @param CardGraphic[] $hand
     * @return array<string, mixed>
     */
    public function evaluateHand(array $hand): array
    {
        // Transform aces (1) to 14 for evaluation
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);

        usort($hand, fn ($acard, $bcard) => $acard->getValue() - $bcard->getValue());

        $rankings = [
            'isFullHouse' => 'Full House',
            'isFlush' => 'Flush',
            'isStraight' => 'Straight',
            'isThreeOfAKind' => 'Three of a Kind',
            'isTwoPair' => 'Two Pair',
            'isOnePair' => 'One Pair',
        ];

        foreach ($rankings as $method => $rank) {
            $result = $this->$method($hand);
            if ($result !== null) {
                return ['rank' => $rank, 'values' => $result];
            }
        }

        // Ensure 'values' is always an array, representing the card values in descending order
        $values = array_reverse(array_map(fn ($card) => $card->getValue(), $hand));
        return ['rank' => 'High Card', 'values' => $values];
    }

    /**
     * Adjust the value of an Ace from 1 to 14 for evaluation purposes.
     *
     * @param CardGraphic $card
     * @return CardGraphic
     */
    private function adjustAceValue(CardGraphic $card): CardGraphic
    {
        // If the card value is 1 (Ace), treat it as 14 for Texas Hold'em
        if ($card->getValue() === 1) {
            $card = new CardGraphic(14, $card->getSuit()); // Create a new card with value 14
        }
        return $card;
    }

    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isFullHouse(array $hand): ?array
    {
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $values = array_map(fn ($card) => (int) $card->getValue(), $hand);
        $counts = array_count_values($values);

        $threeOfAKind = null;
        $pair = null;

        foreach ($counts as $value => $count) {
            if ($count === 3) {
                $threeOfAKind = (int) $value;
            } elseif ($count === 2) {
                $pair = (int) $value;
            }
        }

        return $threeOfAKind !== null && $pair !== null
            ? array_merge([$threeOfAKind, $pair], array_diff($values, [$threeOfAKind, $pair]))
            : null;
    }

    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isFlush(array $hand): ?array
    {
        if (count($hand) < 5) {
            return null;
        }

        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $suits = array_map(fn ($card) => $card->getSuit(), $hand);
        $uniqueSuits = array_unique($suits);

        if (count($uniqueSuits) === 1) {
            // Return the card values in descending order
            return array_reverse(array_map(fn ($card) => $card->getValue(), $hand));
        }

        return null;
    }

    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isStraight(array $hand): ?array
    {
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $values = array_map(fn ($card) => $card->getValue(), $hand);
        sort($values);

        $valueCount = count($values);
        for ($i = 0; $i < $valueCount - 1; $i++) {
            if ($values[$i + 1] !== $values[$i] + 1) {
                return null;
            }
        }

        // Return the card values in descending order
        return array_reverse($values);
    }

    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isThreeOfAKind(array $hand): ?array
    {
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $values = array_map(fn ($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        foreach ($counts as $value => $count) {
            if ($count === 3) {
                $remainingValues = array_values(array_diff($values, [$value]));
                // Return the three of a kind first, followed by the kickers in descending order
                return array_merge([$value, $value, $value], array_reverse($remainingValues));
            }
        }

        return null;
    }


    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isTwoPair(array $hand): ?array
    {
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $values = array_map(fn ($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        $pairs = [];

        foreach ($counts as $value => $count) {
            if ($count === 2) {
                $pairs[] = $value;
            }
        }

        if (count($pairs) === 2) {
            $remainingValue = array_values(array_diff($values, $pairs))[0];
            // Sort pairs and return them in descending order, followed by the remaining kicker
            rsort($pairs);
            return array_merge([$pairs[0], $pairs[0], $pairs[1], $pairs[1]], [$remainingValue]);
        }

        return null;
    }

    /**
     * @param CardGraphic[] $hand
     * @return ?array<int>
     */
    public function isOnePair(array $hand): ?array
    {
        $hand = array_map(fn ($card) => $this->adjustAceValue($card), $hand);
        $values = array_map(fn ($card) => $card->getValue(), $hand);
        $counts = array_count_values($values);

        foreach ($counts as $value => $count) {
            if ($count === 2) {
                $remainingValues = array_values(array_diff($values, [$value]));
                // Return the pair first, followed by the kickers in descending order
                return array_merge([$value, $value], array_reverse($remainingValues));
            }
        }

        return null;
    }

}
