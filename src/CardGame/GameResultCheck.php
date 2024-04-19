<?php

namespace App\CardGame;

class GameResultCheck
{
    /**
     * @param array{high: int, low: int} $totals
     * @return bool
     */
    public function checkBlackjack(array $totals): bool
    {
        return $totals['high'] === 21 || $totals['low'] === 21;
    }

    /**
     * @param array{high: int, low: int} $totals
     * @return bool
     */
    private function checkBust(array $totals): bool
    {
        return $totals['high'] > 21 && $totals['low'] > 21;
    }

    /**
     * @param array{high: int, low: int} $playerTotals
     * @param array{high: int, low: int} $dealerTotals
     * @return string
     */
    public function blackjackOrBust(array $playerTotals, array $dealerTotals): string
    {
        $conditions = [
            ['check' => 'Bust', 'result' => 'Bust! Dealer wins!', 'player' => true, 'dealer' => true],
            ['check' => 'Blackjack', 'result' => 'You win!', 'player' => true, 'dealer' => false],
            ['check' => 'Blackjack', 'result' => 'Dealer wins!', 'player' => false, 'dealer' => true],
            ['check' => 'Blackjack', 'result' => 'It\'s a tie!', 'player' => true, 'dealer' => true],
            ['check' => 'Blackjack', 'result' => 'Dealer busts! You win!', 'player' => true, 'dealer' => false],
            ['check' => 'Blackjack', 'result' => 'You bust! Dealer wins!', 'player' => false, 'dealer' => true],
            ['check' => 'Blackjack', 'result' => 'It\'s a tie!', 'player' => true, 'dealer' => true],
            ['check' => '', 'result' => '', 'player' => false, 'dealer' => false],
        ];

        foreach ($conditions as $condition) {
            if ($this->checkCondition($condition['check'], $playerTotals) && $this->checkCondition($condition['check'], $dealerTotals)) {
                return $condition['result'];
            } elseif ($this->checkCondition($condition['check'], $playerTotals) && !$this->checkCondition($condition['check'], $dealerTotals)) {
                return $condition['result'];
            } elseif (!$this->checkCondition($condition['check'], $playerTotals) && $this->checkCondition($condition['check'], $dealerTotals)) {
                return $condition['result'];
            }
        }

        return '';
    }

    /**
     * @param array{high: int, low: int} $totals
     * @return bool
     */
    public function checkCondition(string $condition, array $totals): bool
    {
        switch ($condition) {
            case 'Bust':
                return $this->checkBust($totals);
            case 'Blackjack':
                return $this->checkBlackjack($totals);
            default:
                return false;
        }
    }


    /**
     * @param array{high: int, low: int} $playerTotals
     * @param array{high: int, low: int} $dealerTotals
     * @return string
     */
    public function highestScore(array $playerTotals, array $dealerTotals): string
    {
        $playerHighest = ($playerTotals['high'] <= 21) ? $playerTotals['high'] : $playerTotals['low'];
        $dealerHighest = ($dealerTotals['high'] <= 21) ? $dealerTotals['high'] : $dealerTotals['low'];

        if ($playerHighest > 21) {
            return 'Dealer wins!';
        }

        if ($dealerHighest > 21 || $playerHighest > $dealerHighest) {
            return 'You win!';
        }

        if ($dealerHighest > $playerHighest) {
            return 'Dealer wins!';
        }

        return 'It\'s a tie!';
    }
}
