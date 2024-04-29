<?php

namespace App\CardGame;

use PHPUnit\Framework\TestCase;

class GameResultCheckTest extends TestCase
{
    public function testCheckBlackjack(): void
    {
        $gameResultCheck = new GameResultCheck();

        // Test when totals contain blackjack
        $totalsWithBJ = ['high' => 21, 'low' => 21];
        $this->assertTrue($gameResultCheck->checkBlackjack($totalsWithBJ));

        // Test when totals do not contain blackjack
        $totalsWithoutBJ = ['high' => 20, 'low' => 20];
        $this->assertFalse($gameResultCheck->checkBlackjack($totalsWithoutBJ));

        // Test when both high and low totals are above 21
        $totalsAbove21 = ['high' => 22, 'low' => 22];
        $this->assertFalse($gameResultCheck->checkBlackjack($totalsAbove21));

        // Test when only one of high or low total equals 21
        $oneTotalEqual21 = ['high' => 21, 'low' => 20];
        $this->assertTrue($gameResultCheck->checkBlackjack($oneTotalEqual21));

        // Test when both high and low totals are below 21 but not equal to 21
        $totalsBelow21 = ['high' => 19, 'low' => 18];
        $this->assertFalse($gameResultCheck->checkBlackjack($totalsBelow21));
    }

    public function testCheckBust(): void
    {
        $gameResultCheck = new GameResultCheck();

        // Test when totals are bust
        $totalsBust = ['high' => 22, 'low' => 23];
        $this->assertTrue($gameResultCheck->checkBust($totalsBust));

        // Test when totals are not bust
        $totalsNotBust = ['high' => 20, 'low' => 18];
        $this->assertFalse($gameResultCheck->checkBust($totalsNotBust));

        // Test when both high and low totals are below 21
        $totalsBelow21 = ['high' => 19, 'low' => 18];
        $this->assertFalse($gameResultCheck->checkBust($totalsBelow21));

        // Test when both high and low totals are above 21
        $totalsAbove21 = ['high' => 22, 'low' => 23];
        $this->assertTrue($gameResultCheck->checkBust($totalsAbove21));

        // Test when only one of high or low total equals 21
        $oneTotalEqual21 = ['high' => 21, 'low' => 18];
        $this->assertFalse($gameResultCheck->checkBust($oneTotalEqual21));
    }

    public function testBlackjackOrBust(): void
    {
        $gameResultCheck = new GameResultCheck();

        // Test when both player and dealer bust
        $playerTotalsBust = ['high' => 22, 'low' => 23];
        $dealerTotalsBust = ['high' => 24, 'low' => 25];
        $this->assertEquals('It\'s a tie!', $gameResultCheck->blackjackOrBust($playerTotalsBust, $dealerTotalsBust));

        // Test when player busts and dealer doesn't
        $playerTotalsBust = ['high' => 22, 'low' => 23];
        $dealerTotalsNotBust = ['high' => 18, 'low' => 20];
        $this->assertEquals('Bust! Dealer wins!', $gameResultCheck->blackjackOrBust($playerTotalsBust, $dealerTotalsNotBust));

        // Test when dealer busts and player doesn't
        $playerTotalsNotBust = ['high' => 19, 'low' => 20];
        $dealerTotalsBust = ['high' => 22, 'low' => 23];
        $this->assertEquals('Dealer busts! You win!', $gameResultCheck->blackjackOrBust($playerTotalsNotBust, $dealerTotalsBust));

        // Test when neither player nor dealer busts
        $playerTotalsNotBust = ['high' => 18, 'low' => 19];
        $dealerTotalsNotBust = ['high' => 17, 'low' => 18];
        $this->assertEquals('', $gameResultCheck->blackjackOrBust($playerTotalsNotBust, $dealerTotalsNotBust));

        // Test when both player and dealer have blackjack
        $playerTotalsBJ = ['high' => 21, 'low' => 21];
        $dealerTotalsBJ = ['high' => 21, 'low' => 21];
        $this->assertEquals("It's a tie!", $gameResultCheck->blackjackOrBust($playerTotalsBJ, $dealerTotalsBJ));

        // Test when only player has blackjack
        $playerTotalsBJ = ['high' => 21, 'low' => 21];
        $dealerTotalsNotBJ = ['high' => 20, 'low' => 18];
        $this->assertEquals('You win!', $gameResultCheck->blackjackOrBust($playerTotalsBJ, $dealerTotalsNotBJ));

        // Test when only dealer has blackjack
        $playerTotalsNotBJ = ['high' => 20, 'low' => 18];
        $dealerTotalsBJ = ['high' => 21, 'low' => 21];
        $this->assertEquals('Dealer wins!', $gameResultCheck->blackjackOrBust($playerTotalsNotBJ, $dealerTotalsBJ));
    }

    public function testHighestScore(): void
    {
        $gameResultCheck = new GameResultCheck();

        // Test when player has a higher score than the dealer
        $playerHighScore = ['high' => 19, 'low' => 19];
        $dealerLowScore = ['high' => 18, 'low' => 18];
        $this->assertEquals('You win!', $gameResultCheck->highestScore($playerHighScore, $dealerLowScore));

        // Test when dealer has a higher score than the player
        $playerLowScore = ['high' => 17, 'low' => 17];
        $dealerHighScore = ['high' => 19, 'low' => 19];
        $this->assertEquals('Dealer wins!', $gameResultCheck->highestScore($playerLowScore, $dealerHighScore));

        // Test when both player and dealer have the same score
        $playerTieScore = ['high' => 18, 'low' => 18];
        $dealerTieScore = ['high' => 18, 'low' => 18];
        $this->assertEquals("It's a tie!", $gameResultCheck->highestScore($playerTieScore, $dealerTieScore));

        // Test when player has a higher low score but dealer has a higher high score
        $playerHighLowScore = ['high' => 17, 'low' => 19];
        $dealerHighHighScore = ['high' => 20, 'low' => 16];
        $this->assertEquals('Dealer wins!', $gameResultCheck->highestScore($playerHighLowScore, $dealerHighHighScore));

        // Test when player has a higher high score but dealer has a higher low score
        $playerHighHighScore = ['high' => 20, 'low' => 16];
        $dealerHighLowScore = ['high' => 17, 'low' => 19];
        $this->assertEquals('You win!', $gameResultCheck->highestScore($playerHighHighScore, $dealerHighLowScore));

        // Test when player has a high score over 21
        $playerBust = ['high' => 22, 'low' => 18];
        $dealerNotBust = ['high' => 19, 'low' => 20];
        $this->assertEquals('Dealer wins!', $gameResultCheck->highestScore($playerBust, $dealerNotBust));
    }

    public function testCheckCondition(): void
    {
        $gameResultCheck = new GameResultCheck();

        // Test when condition is 'Bust' and totals indicate bust
        $totalsBust = ['high' => 22, 'low' => 23];
        $this->assertTrue($gameResultCheck->checkCondition('Bust', $totalsBust));

        // Test when condition is 'Bust' and totals do not indicate bust
        $totalsNotBust = ['high' => 19, 'low' => 20];
        $this->assertFalse($gameResultCheck->checkCondition('Bust', $totalsNotBust));

        // Test when condition is 'Blackjack' and totals indicate blackjack
        $totalsBlackjack = ['high' => 21, 'low' => 21];
        $this->assertTrue($gameResultCheck->checkCondition('Blackjack', $totalsBlackjack));

        // Test when condition is 'Blackjack' and totals do not indicate blackjack
        $totalsNotBlackjack = ['high' => 19, 'low' => 20];
        $this->assertFalse($gameResultCheck->checkCondition('Blackjack', $totalsNotBlackjack));

        // Test when condition is unknown
        $totals = ['high' => 19, 'low' => 20];
        $this->assertFalse($gameResultCheck->checkCondition('Unknown', $totals));
    }
}
