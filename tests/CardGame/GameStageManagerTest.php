<?php

namespace App\Tests\CardGame;

use App\CardGame\GameStageManager;
use App\CardGame\CommunityCardManager;
use App\CardGame\Deck;
use PHPUnit\Framework\TestCase;

class GameStageManagerTest extends TestCase
{
    public function testInitialStageIsPreFlop(): void
    {
        $gameStageManager = new GameStageManager();
        $this->assertEquals(0, $gameStageManager->getCurrentStage(), "Initial stage should be Pre-Flop (stage 0).");
    }

    public function testAdvanceStageToFlop(): void
    {
        $gameStageManager = new GameStageManager();
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Expect the dealCommunityCards method to be called with 3 cards for the Flop
        $communityCardManager->expects($this->once())
            ->method('dealCommunityCards')
            ->with(3);

        // Advance to Flop
        $gameStageManager->advanceStage($communityCardManager);

        $this->assertEquals(1, $gameStageManager->getCurrentStage(), "Stage should be Flop (stage 1) after advancement.");
    }

    public function testAdvanceStageToTurn(): void
    {
        $gameStageManager = new GameStageManager();
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Advance to Flop
        /** @scrutinizer ignore-deprecated */ $communityCardManager->expects($this->exactly(2))
            ->method('dealCommunityCards')
            ->withConsecutive([3], [1]);

        // Advance to Flop and Turn
        $gameStageManager->advanceStage($communityCardManager); // Flop
        $gameStageManager->advanceStage($communityCardManager); // Turn

        $this->assertEquals(2, $gameStageManager->getCurrentStage(), "Stage should be Turn (stage 2) after advancement.");
    }

    public function testAdvanceStageToRiver(): void
    {
        $gameStageManager = new GameStageManager();
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Advance to Flop, Turn, and River
        /** @scrutinizer ignore-deprecated */ $communityCardManager->expects($this->exactly(3))
            ->method('dealCommunityCards')
            ->withConsecutive([3], [1], [1]);

        $gameStageManager->advanceStage($communityCardManager); // Flop
        $gameStageManager->advanceStage($communityCardManager); // Turn
        $gameStageManager->advanceStage($communityCardManager); // River

        $this->assertEquals(3, $gameStageManager->getCurrentStage(), "Stage should be River (stage 3) after advancement.");
    }

    public function testIsFinalStage(): void
    {
        $gameStageManager = new GameStageManager();
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Advance to River
        $gameStageManager->advanceStage($communityCardManager); // Flop
        $gameStageManager->advanceStage($communityCardManager); // Turn
        $gameStageManager->advanceStage($communityCardManager); // River

        $this->assertTrue($gameStageManager->isFinalStage(), "Stage should be final after River (stage 3).");
    }

    public function testResetStage(): void
    {
        $gameStageManager = new GameStageManager();
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Advance to River
        $gameStageManager->advanceStage($communityCardManager); // Flop
        $gameStageManager->advanceStage($communityCardManager); // Turn
        $gameStageManager->advanceStage($communityCardManager); // River

        // Reset the stage
        $gameStageManager->resetStage();

        $this->assertEquals(0, $gameStageManager->getCurrentStage(), "Stage should be reset to Pre-Flop (stage 0).");
        $this->assertFalse($gameStageManager->isFinalStage(), "After reset, the stage should no longer be final.");
    }
}
