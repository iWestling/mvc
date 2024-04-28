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
    public function checkBust(array $totals): bool
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
        if ($this->checkBust($playerTotals) && $this->checkBust($dealerTotals)) {
            return 'It\'s a tie!';
        } elseif ($this->checkBust($playerTotals)) {
            return 'Bust! Dealer wins!';
        } elseif ($this->checkBust($dealerTotals)) {
            return 'Dealer busts! You win!';
        } elseif ($this->checkBlackjack($playerTotals) && $this->checkBlackjack($dealerTotals)) {
            return 'It\'s a tie!';
        } elseif ($this->checkBlackjack($playerTotals)) {
            return 'You win!';
        } elseif ($this->checkBlackjack($dealerTotals)) {
            return 'Dealer wins!';
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

        if ($playerHighest > $dealerHighest) {
            return 'You win!';
        }

        if ($dealerHighest > $playerHighest) {
            return 'Dealer wins!';
        }

        return "It's a tie!";
    }

}
