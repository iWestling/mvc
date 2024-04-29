<?php

namespace App\CardGame;

/**
 * Methods for checking game results
 */
class GameResultCheck
{
    /**
     * Checks if total score indicates blackjack
     *
     * @param array{high: int, low: int} $totals The total score to check
     * @return bool True if total score indicates blackjack, otherwise false
     */
    public function checkBlackjack(array $totals): bool
    {
        return $totals['high'] === 21 || $totals['low'] === 21;
    }

    /**
     * Checks if total score indicates bust
     *
     * @param array{high: int, low: int} $totals Total score to check
     * @return bool True if total score indicates bust, otherwise false
     */
    public function checkBust(array $totals): bool
    {
        return $totals['high'] > 21 && $totals['low'] > 21;
    }

    /**
     * Determines the result of the game based on the player and dealer totals
     *
     * @param array{high: int, low: int} $playerTotals Player's total score.
     * @param array{high: int, low: int} $dealerTotals Dealer's total score.
     * @return string Result of the game
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
     * Checks if total score meets the specified condition
     *
     * @param string $condition The condition to check (Bust or blackjack)
     * @param array{high: int, low: int} $totals The total score to check
     * @return bool True if total score meets the condition, otherwise false
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
     * Determines the winner based on the highest score between player and dealer
     *
     * @param array{high: int, low: int} $playerTotals player's total score
     * @param array{high: int, low: int} $dealerTotals dealer's total score
     * @return string The result of the comparison
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
