<?php

namespace App\Tests\CardGame;

use App\CardGame\PlayerActionInit;
use App\CardGame\Player;
use App\CardGame\PotManager;
use PHPUnit\Framework\TestCase;

class PlayerActionInitTest extends TestCase
{
    private PotManager $potManager;
    private PlayerActionInit $actionInit;

    protected function setUp(): void
    {
        $this->potManager = new PotManager();
        $this->actionInit = new PlayerActionInit($this->potManager);
    }

    public function testHandleBlinds(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $player3 = new Player('Player 3', 1000, 'normal');

        $player1->setRole('dealer');
        $player2->setRole('small blind');
        $player3->setRole('big blind');

        $this->actionInit->handleBlinds([$player1, $player2, $player3]);

        $this->assertEquals(2, $player2->getCurrentBet()); // Small blind bet 2 chips
        $this->assertEquals(4, $player3->getCurrentBet()); // Big blind bet 4 chips
        $this->assertEquals(6, $this->potManager->getPot()); // Pot should be 6 chips
    }

    public function testHandleCall(): void
    {
        $this->potManager->updateCurrentBet(100);

        $player = new Player('Player 1', 1000, 'normal');
        $player->setCurrentBet(50);

        $this->actionInit->handleCall($player);

        $this->assertEquals(100, $player->getCurrentBet()); // Player should have matched the current bet
        $this->assertEquals(50, $this->potManager->getPot()); // 50 chips added to the pot
    }

    public function testHandleRaise(): void
    {
        $player = new Player('Player 1', 1000, 'normal');

        $this->actionInit->handleRaise($player, 100);

        $this->assertEquals(100, $player->getCurrentBet()); // Player should have bet 100 chips
        $this->assertEquals(100, $this->potManager->getPot()); // Pot should be 100 chips
        $this->assertEquals(100, $this->potManager->getCurrentBet()); // Current bet should be updated to 100
    }

    public function testHandleCheck(): void
    {
        $player = new Player('Player 1', 1000, 'normal');

        $this->actionInit->handleCheck($player);

        $this->assertTrue(true);
    }

    public function testHandleAllIn(): void
    {
        $player = new Player('Player 1', 500, 'normal');

        $this->actionInit->handleAllIn($player);

        $this->assertEquals(0, $player->getChips()); // Player should be out of chips
        $this->assertEquals(500, $this->potManager->getPot()); // Pot should be 500 chips
        $this->assertEquals(500, $this->potManager->getCurrentBet()); // Current bet should be updated to 500
    }

    public function testResetCurrentBet(): void
    {
        $player = new Player('Player 1', 1000, 'normal');
        $player->setCurrentBet(100);

        $this->actionInit->resetCurrentBet($player);

        $this->assertEquals(0, $player->getCurrentBet()); // Player's current bet should be reset to 0
    }

    public function testInitializeRoles(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $player3 = new Player('Player 3', 1000, 'normal');

        $this->actionInit->initializeRoles([$player1, $player2, $player3], 0);

        $this->assertEquals('dealer', $player1->getRole());
        $this->assertEquals('small blind', $player2->getRole());
        $this->assertEquals('big blind', $player3->getRole());
    }

    public function testRotateRoles(): void
    {
        $dealerIndex = 0;
        $this->actionInit->rotateRoles($dealerIndex, 3);
        $this->assertEquals(1, $dealerIndex);

        $this->actionInit->rotateRoles($dealerIndex, 3);
        $this->assertEquals(2, $dealerIndex);

        $this->actionInit->rotateRoles($dealerIndex, 3);
        $this->assertEquals(0, $dealerIndex);
    }

    public function testResetPlayersForNewRound(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 10, 'normal'); // Player with insufficient chips
        $actions = [];

        $this->actionInit->resetPlayersForNewRound([$player1, $player2], $actions);

        $this->assertEquals('No action yet', $actions[$player1->getName()]);
        $this->assertEquals("Player has insufficient chips, you'll need to start a new game.", $actions[$player2->getName()]);
        $this->assertTrue($player2->isFolded()); // Player 2 should be folded due to insufficient chips
    }
}
