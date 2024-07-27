<?php

namespace App\Tests\CardGame;

use App\CardGame\MoneyHandling;
use PHPUnit\Framework\TestCase;

class MoneyHandlingTest extends TestCase
{
    public function testHandleMoneyPlayerWins(): void
    {
        $moneyHandler = new MoneyHandling();
        $gameResult = 'You win!';
        $playerBet = 10;
        $playerMoney = 50;
        $dealerMoney = 50;

        [$newPlayerMoney, $newDealerMoney] = $moneyHandler->handleMoney($gameResult, $playerBet, $playerMoney, $dealerMoney);

        $this->assertEquals(70, $newPlayerMoney); // Player's money should increase by 2 times the bet
        $this->assertEquals(50, $newDealerMoney); // Dealer's money should remain the same
    }

    public function testHandleMoneyDealerWins(): void
    {
        $moneyHandler = new MoneyHandling();
        $gameResult = 'Dealer wins!';
        $playerBet = 10;
        $playerMoney = 50;
        $dealerMoney = 50;

        [$newPlayerMoney, $newDealerMoney] = $moneyHandler->handleMoney($gameResult, $playerBet, $playerMoney, $dealerMoney);

        $this->assertEquals(50, $newPlayerMoney); // Player's money should remain the same
        $this->assertEquals(70, $newDealerMoney); // Dealer's money should increase by 2 times the bet
    }

    public function testHandleMoneyTie(): void
    {
        $moneyHandler = new MoneyHandling();
        $gameResult = 'It\'s a tie!';
        $playerBet = 10;
        $playerMoney = 50;
        $dealerMoney = 50;

        [$newPlayerMoney, $newDealerMoney] = $moneyHandler->handleMoney($gameResult, $playerBet, $playerMoney, $dealerMoney);

        $this->assertEquals(60, $newPlayerMoney); // Player's money should increase by the bet
        $this->assertEquals(60, $newDealerMoney); // Dealer's money should increase by the bet
    }

    public function testHandleMoneyNoChange(): void
    {
        $moneyHandler = new MoneyHandling();
        $gameResult = 'Some other result';
        $playerBet = 10;
        $playerMoney = 50;
        $dealerMoney = 50;

        [$newPlayerMoney, $newDealerMoney] = $moneyHandler->handleMoney($gameResult, $playerBet, $playerMoney, $dealerMoney);

        $this->assertEquals(50, $newPlayerMoney); // Player's money should remain the same
        $this->assertEquals(50, $newDealerMoney); // Dealer's money should remain the same
    }
}
