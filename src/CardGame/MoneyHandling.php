<?php

namespace App\CardGame;

/**
 * Methods for handling the money in the game
 */
class MoneyHandling
{
    /**
     * Changes the money based on game result
     *
     * @param string $gameResult Result of the game
     * @param int $playerBet Player's bet amount.
     * @param int $playerMoney Player's current money.
     * @param int $dealerMoney Dealer's current money.
     * @return array{int, int} The updated player and dealer money.
     */
    public function handleMoney(string $gameResult, int $playerBet, int $playerMoney, int $dealerMoney): array
    {
        switch ($gameResult) {
            case 'Dealer busts! You win!':
            case 'You win!':
                $playerMoney += $playerBet * 2;
                return [(int) $playerMoney, (int) $dealerMoney];
            case 'Bust! Dealer wins!':
            case 'Dealer wins!':
                $dealerMoney += $playerBet * 2;
                return [(int) $playerMoney, (int) $dealerMoney];
            case 'It\'s a tie!':
                $dealerMoney += $playerBet;
                $playerMoney += $playerBet;
                return [(int) $playerMoney, (int) $dealerMoney];
            default:
                return [(int) $playerMoney, (int) $dealerMoney];
        }
    }
}
