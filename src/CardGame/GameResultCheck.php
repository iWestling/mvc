<?php

namespace App\CardGame;

class GameResultCheck
{
    public static function blackjackOrBust($playerTotals, $dealerTotals)
    {
        if ($playerTotals['high'] > 21 && $playerTotals['low'] > 21) {
            return 'Bust! Dealer wins!';
        }
        if ($dealerTotals['high'] > 21 && $dealerTotals['low'] > 21) {
            return 'Dealer busts! You win!';
        }

        if ($playerTotals['high'] === 21 || $playerTotals['low'] === 21) {
            if ($dealerTotals['high'] === 21 || $dealerTotals['low'] === 21) {
                return 'It\'s a tie!';
            } else {
                return 'You win!';
            }
        }

        if ($dealerTotals['high'] === 21 || $dealerTotals['low'] === 21) {
            if ($playerTotals['high'] === 21 || $playerTotals['low'] === 21) {
                return 'It\'s a tie!';
            } else {
                return 'Dealer wins!';
            }
        }

        return '';
    }
    public static function highestScore($playerTotals, $dealerTotals)
    {

        if ($playerTotals['high'] <= 21 && $dealerTotals['high'] <= 21){
            if ($dealerTotals['high'] > $playerTotals['high']) {
                return 'Dealer wins!';
            }
            if ($playerTotals['high'] > $dealerTotals['high']) {
                return 'You win!';
            }
            if ($playerTotals['high'] === $dealerTotals['high']) {
                return 'It\'s a tie!';
            }
        }
        if ($playerTotals['high'] <= 21 && $dealerTotals['low'] <= 21){
            if ($dealerTotals['low'] > $playerTotals['high']) {
                return 'Dealer wins!';
            }
            if ($playerTotals['high'] > $dealerTotals['low']) {
                return 'You win!';
            }
            if ($playerTotals['high'] === $dealerTotals['low']) {
                return 'It\'s a tie!';
            }
        }
        if ($playerTotals['low'] <= 21 && $dealerTotals['high'] <= 21){
            if ($dealerTotals['high'] > $playerTotals['low']) {
                return 'Dealer wins!';
            }
            if ($playerTotals['low'] > $dealerTotals['high']) {
                return 'You win!';
            }
            if ($playerTotals['low'] === $dealerTotals['high']) {
                return 'It\'s a tie!';
            }
        }
        if ($playerTotals['low'] <= 21 && $dealerTotals['low'] <= 21){
            if ($dealerTotals['low'] > $playerTotals['low']) {
                return 'Dealer wins!';
            }
            if ($playerTotals['low'] > $dealerTotals['low']) {
                return 'You win!';
            }
            if ($playerTotals['low'] === $dealerTotals['low']) {
                return 'It\'s a tie!';
            }
        }

        return '';
    }
}
