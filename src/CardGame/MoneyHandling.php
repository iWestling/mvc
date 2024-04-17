<?php

namespace App\CardGame;

class MoneyHandling
{
    public static function handleMoney(string $gameResult, int $playerBet, int $playerMoney, int $dealerMoney): array
    {
        switch ($gameResult) {
            case 'Dealer busts! You win!':
            case 'You win!':
                $playerMoney += $playerBet * 2;
                return [$playerMoney, $dealerMoney];
            case 'Bust! Dealer wins!':
            case 'Dealer wins!':
                $dealerMoney += $playerBet * 2;
                return [$playerMoney, $dealerMoney];
            case 'It\'s a tie!':
                $dealerMoney += $playerBet;
                $playerMoney += $playerBet;
                return [$playerMoney, $dealerMoney];
            default:
                return [$playerMoney, $dealerMoney];
        }
    }
}
