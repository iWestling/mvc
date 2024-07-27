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
     * @return int[] The updated player and dealer money as an array with two integers.
     */
    public function handleMoney(string $gameResult, int $playerBet, int $playerMoney, int $dealerMoney): array
    {
        switch ($gameResult) {
            case 'Dealer busts! You win!':
            case 'You win!':
                $playerMoney += (int)($playerBet * 2);
                return [$playerMoney, $dealerMoney];
            case 'Bust! Dealer wins!':
            case 'Dealer wins!':
                $dealerMoney += (int)($playerBet * 2);
                return [$playerMoney, $dealerMoney];
            case 'It\'s a tie!':
                $dealerMoney += (int)$playerBet;
                $playerMoney += (int)$playerBet;
                return [$playerMoney, $dealerMoney];
            default:
                return [$playerMoney, $dealerMoney];
        }
    }
}
