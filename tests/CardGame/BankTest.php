<?php

namespace App\Tests\CardGame;

use App\CardGame\Bank;
use App\CardGame\Player;
use PHPUnit\Framework\TestCase;

class BankTest extends TestCase
{
    public function testPlaceBetIncreasesPlayerBetAndPot(): void
    {
        $bank = new Bank();
        $player = new Player('Player1', 1000, 'normal');

        $bank->placeBet($player, 100);

        $this->assertEquals(100, $bank->getPlayerBet($player));
        $this->assertEquals(100, $bank->getPot());
    }

    public function testPlaceMultipleBetsIncreasesPlayerBetAndPot(): void
    {
        $bank = new Bank();
        $player = new Player('Player1', 1000, 'normal');

        $bank->placeBet($player, 100);
        $bank->placeBet($player, 200);

        $this->assertEquals(300, $bank->getPlayerBet($player));
        $this->assertEquals(300, $bank->getPot());
    }

    public function testPlaceBetFromMultiplePlayersIncreasesPot(): void
    {
        $bank = new Bank();
        $player1 = new Player('Player1', 1000, 'normal');
        $player2 = new Player('Player2', 1000, 'normal');

        $bank->placeBet($player1, 100);
        $bank->placeBet($player2, 200);

        $this->assertEquals(100, $bank->getPlayerBet($player1));
        $this->assertEquals(200, $bank->getPlayerBet($player2));
        $this->assertEquals(300, $bank->getPot());
    }

    public function testGetPlayerBetReturnsZeroIfNoBetPlaced(): void
    {
        $bank = new Bank();
        $player = new Player('Player1', 1000, 'normal');

        $this->assertEquals(0, $bank->getPlayerBet($player));
    }

    public function testResetBetsClearsAllBetsButNotPot(): void
    {
        $bank = new Bank();
        $player1 = new Player('Player1', 1000, 'normal');
        $player2 = new Player('Player2', 1000, 'normal');

        $bank->placeBet($player1, 100);
        $bank->placeBet($player2, 200);

        $bank->resetBets();

        $this->assertEquals(0, $bank->getPlayerBet($player1));
        $this->assertEquals(0, $bank->getPlayerBet($player2));
        $this->assertEquals(300, $bank->getPot());
    }
}
