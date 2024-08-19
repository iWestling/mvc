<?php

namespace App\Tests\CardGame;

use App\CardGame\PotManager;
use App\CardGame\Player;
use PHPUnit\Framework\TestCase;

class PotManagerTest extends TestCase
{
    private PotManager $potManager;

    protected function setUp(): void
    {
        $this->potManager = new PotManager();
    }

    public function testAddToPot(): void
    {
        $this->potManager->addToPot(100);
        $this->assertEquals(100, $this->potManager->getPot());

        $this->potManager->addToPot(50);
        $this->assertEquals(150, $this->potManager->getPot());
    }

    public function testResetPot(): void
    {
        $this->potManager->addToPot(100);
        $this->potManager->resetPot();
        $this->assertEquals(0, $this->potManager->getPot());
    }

    public function testUpdateAndResetCurrentBet(): void
    {
        $this->potManager->updateCurrentBet(200);
        $this->assertEquals(200, $this->potManager->getCurrentBet());

        $this->potManager->resetCurrentBet();
        $this->assertEquals(0, $this->potManager->getCurrentBet());
    }

    public function testHaveAllActivePlayersMatchedCurrentBet(): void
    {
        // Create mock players for the first scenario
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        // Setup player 1: Not folded, matched the current bet
        $player1->method('isFolded')
            ->willReturn(false);
        $player1->method('getCurrentBet')
            ->willReturn(200);

        // Setup player 2: Not folded, did not match the current bet
        $player2->method('isFolded')
            ->willReturn(false);
        $player2->method('getCurrentBet')
            ->willReturn(100);

        // Set the current bet in PotManager
        $this->potManager->updateCurrentBet(200);

        // First test case: One player has not matched the current bet
        $this->assertFalse($this->potManager->haveAllActivePlayersMatchedCurrentBet([$player1, $player2]));

        // Create new mock players for the second scenario
        $player3 = $this->createMock(Player::class);
        $player4 = $this->createMock(Player::class);

        // Setup player 3: Not folded, matched the current bet
        $player3->method('isFolded')
            ->willReturn(false);
        $player3->method('getCurrentBet')
            ->willReturn(200);

        // Setup player 4: Not folded, matched the current bet
        $player4->method('isFolded')
            ->willReturn(false);
        $player4->method('getCurrentBet')
            ->willReturn(200);

        // Second test case: All players have matched the current bet
        $this->assertTrue($this->potManager->haveAllActivePlayersMatchedCurrentBet([$player3, $player4]));
    }

    public function testDistributeWinningsToPlayer(): void
    {
        $player = $this->createMock(Player::class);

        $this->potManager->addToPot(300);
        $player->expects($this->once())->method('addChips')->with(300);

        $this->potManager->distributeWinningsToPlayer($player);
    }

    public function testSplitPotAmongWinners(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        $this->potManager->addToPot(400);

        $player1->expects($this->once())->method('addChips')->with(200);
        $player2->expects($this->once())->method('addChips')->with(200);

        $this->potManager->splitPotAmongWinners([$player1, $player2]);
    }
}
