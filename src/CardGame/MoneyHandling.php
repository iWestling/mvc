<?php

namespace App\CardGame;

class MoneyHandling
{
    /**
     * @param string $gameResult
     * @param int $playerBet
     * @param int $playerMoney
     * @param int $dealerMoney
     * @return array{int, int}
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
