<?php

namespace App\Tests\CardGame;

use App\CardGame\AllInScenarioHandler;
use App\CardGame\TexasHoldemGame;
use App\CardGame\PlayerActionHandler;
use PHPUnit\Framework\TestCase;

class AllInScenarioHandlerTest extends TestCase
{
    public function testHandleAllInScenarioWhenAllInOccurs(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $actionHandler = $this->createMock(PlayerActionHandler::class);

        // Mock the game to simulate an All-In scenario
        $game->expects($this->once())
            ->method('hasAllInOccurred')
            ->willReturn(true);

        // Expect handleRemainingPlayersAfterAllIn to be called
        $game->expects($this->once())
            ->method('handleRemainingPlayersAfterAllIn')
            ->with($actionHandler);

        // Mock advancing the game stages
        $game->expects($this->exactly(2))
            ->method('advanceGameStage');

        // Mock the game over status to allow two stage advancements
        $game->expects($this->exactly(3))
            ->method('isGameOver')
            ->willReturnOnConsecutiveCalls(false, false, true);

        $handler = new AllInScenarioHandler();

        $result = $handler->handleAllInScenario($game, $actionHandler);

        $this->assertTrue($result);
    }

    public function testHandleAllInScenarioWhenNoAllInOccurs(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $actionHandler = $this->createMock(PlayerActionHandler::class);

        // Mock the game to simulate no All-In scenario
        $game->expects($this->once())
            ->method('hasAllInOccurred')
            ->willReturn(false);

        $handler = new AllInScenarioHandler();

        $result = $handler->handleAllInScenario($game, $actionHandler);

        $this->assertFalse($result);
    }
}
