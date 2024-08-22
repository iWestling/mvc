<?php

namespace App\Tests\CardGame;

use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\CommunityCardManager;
use App\CardGame\Deck;
use App\CardGame\PotManager;
use App\CardGame\GameStageManager;
use App\CardGame\PlayerActionHandler;
use App\CardGame\PlayerActionInit;
use App\CardGame\WinnerEvaluator;
use App\CardGame\HandEvaluator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class TexasHoldemGameTest extends TestCase
{
    private TexasHoldemGame $game;

    // private $mockActionHandler;

    protected function setUp(): void
    {
        $this->game = new TexasHoldemGame();
        // $this->mockActionHandler = $this->createMock(PlayerActionHandler::class);
    }

    public function testAddPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $this->game->addPlayer($player);

        $players = $this->game->getPlayers();
        $this->assertCount(1, $players);
        $this->assertSame($player, $players[0]);
    }
    public function testGetHandEvaluator(): void
    {
        // Create a mock HandEvaluator
        $mockHandEvaluator = $this->createMock(HandEvaluator::class);

        // Create a mock WinnerEvaluator and set up the expectation for getHandEvaluator
        $mockWinnerEvaluator = $this->createMock(WinnerEvaluator::class);
        $mockWinnerEvaluator->expects($this->once())
            ->method('getHandEvaluator')
            ->willReturn($mockHandEvaluator);

        // Inject the mock WinnerEvaluator into TexasHoldemGame
        $reflection = new ReflectionClass(TexasHoldemGame::class);
        $winnerEvaluProp = $reflection->getProperty('winnerEvaluator');
        $winnerEvaluProp->setAccessible(true);
        $winnerEvaluProp->setValue($this->game, $mockWinnerEvaluator);

        // Call the getHandEvaluator method and assert it returns the mock
        $result = $this->game->getHandEvaluator();
        $this->assertSame($mockHandEvaluator, $result);
    }


    public function testAdvanceGameStage(): void
    {
        $this->game->addPlayer($this->createMock(Player::class));
        $this->game->addPlayer($this->createMock(Player::class));

        $this->game->advanceGameStage();

        $this->assertSame(1, $this->game->getStageManager()->getCurrentStage());
    }

    public function testDetermineWinnerWithSingleActivePlayer(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);
        $player1->method('isFolded')->willReturn(false);
        $player2->method('isFolded')->willReturn(true);

        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->game->determineWinner();

        $winners = $this->game->getWinners();
        $this->assertCount(1, $winners);
        $this->assertSame($player1, $winners[0]);
    }

    public function testHandleActionCall(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getName')->willReturn('Player 1');

        $player->expects($this->any())->method('isFolded')->willReturn(false);

        $this->game->addPlayer($player);

        $this->game->handleAction($player, 'call');

        $actions = $this->game->getActions();
        $this->assertEquals('Call', $actions['Player 1']);
    }


    public function testStartNewRound(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getChips')->willReturn(100);
        $this->game->addPlayer($player);

        $this->game->startNewRound();

        $this->assertFalse($this->game->isGameOver());
        $this->assertEquals(0, $this->game->getStageManager()->getCurrentStage());
        $this->assertCount(0, $this->game->getWinners());
    }

    public function testSetAllInOccurred(): void
    {
        $this->game->setAllInOccurred(true);
        $this->assertTrue($this->game->hasAllInOccurred());
    }

    public function testProcessPlayerAction(): void
    {
        $player = $this->createMock(Player::class);
        $this->game->addPlayer($player);

        $mockActionHandler = $this->createMock(PlayerActionHandler::class);

        $mockActionHandler->expects($this->once())
            ->method('processPlayerAction')
            ->with($player, $this->game, 'raise', 100);

        $this->game->processPlayerAction($mockActionHandler, 'raise', 100);
    }


    public function testCountActivePlayers(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        $player1->method('isFolded')->willReturn(false);
        $player2->method('isFolded')->willReturn(true);

        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->assertEquals(1, $this->game->countActivePlayers());
    }

    public function testGetMinimumChips(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        $player1->method('getChips')->willReturn(50);
        $player1->method('isFolded')->willReturn(false);
        $player2->method('getChips')->willReturn(100);
        $player2->method('isFolded')->willReturn(false);

        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->assertEquals(50, $this->game->getMinimumChips());
    }

    public function testGameOverAfterFinalStage(): void
    {
        $this->game->addPlayer($this->createMock(Player::class));
        $this->game->addPlayer($this->createMock(Player::class));

        $stageManager = $this->game->getStageManager();
        for ($i = 0; $i < 3; $i++) {
            $this->game->advanceGameStage();
        }

        $this->assertTrue($stageManager->isFinalStage());
        $this->assertFalse($this->game->isGameOver());

        // Advance to game over after final stage
        $this->game->advanceGameStage();
        $this->assertTrue($this->game->isGameOver());
    }
    public function testPlayRound(): void
    {
        $mockPlayer = $this->createMock(Player::class);
        $this->game->addPlayer($mockPlayer);

        // Mock PlayerActionHandler and set expectations
        /** @var PlayerActionHandler&\PHPUnit\Framework\MockObject\MockObject $mockActionHandler */
        $mockActionHandler = $this->createMock(PlayerActionHandler::class);

        $mockActionHandler->expects($this->once())
            ->method('processActionsInOrder')
            ->with(
                $this->game->getPlayersInOrder(),
                'raise',
                100,
                $this->game->getCommunityCardManager(),
                $this->callback(function ($callback) {
                    return is_callable($callback);
                })
            );

        // Execute the public method that calls processActionsInOrder internally
        $this->game->playRound($mockActionHandler, 'raise', 100);
    }

    public function testDetermineWinnerNoWinnersFound(): void
    {
        // Add two players to the game
        $mockPlayer1 = $this->createMock(Player::class);
        $mockPlayer2 = $this->createMock(Player::class);

        $mockPlayer1->method('isFolded')->willReturn(false);
        $mockPlayer2->method('isFolded')->willReturn(false);

        $this->game->addPlayer($mockPlayer1);
        $this->game->addPlayer($mockPlayer2);

        // Mock the WinnerEvaluator to return an empty array (no winners found)
        $mockWinnerEvaluator = $this->createMock(WinnerEvaluator::class);
        $mockWinnerEvaluator->expects($this->once())
            ->method('determineWinners')
            ->willReturn([]); // Simulate no winners found

        // Inject the mock WinnerEvaluator into TexasHoldemGame
        $reflection = new ReflectionClass(TexasHoldemGame::class);
        $winnerEvaluatorProp = $reflection->getProperty('winnerEvaluator');
        $winnerEvaluatorProp->setAccessible(true);
        $winnerEvaluatorProp->setValue($this->game, $mockWinnerEvaluator);

        // Expect an exception to be thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No winners found in the game, something went wrong.');

        $this->game->determineWinner();
    }


    public function testHandleAllIn(): void
    {
        $mockPlayer = $this->createMock(Player::class);
        $mockPlayer->method('getName')->willReturn('Player 1');
        $this->game->addPlayer($mockPlayer);

        // Mock PlayerActionInit to verify that handleAllIn is called
        $mockActionInit = $this->createMock(PlayerActionInit::class);
        $mockActionInit->expects($this->once())
            ->method('handleAllIn')
            ->with($mockPlayer);

        $reflection = new ReflectionClass(TexasHoldemGame::class);
        $actionInitProperty = $reflection->getProperty('actionInit');
        $actionInitProperty->setAccessible(true);
        $actionInitProperty->setValue($this->game, $mockActionInit);

        // Mock PlayerActionHandler to verify handleRemainingPlayersAfterAllIn is called
        $mockActionHandler = $this->createMock(PlayerActionHandler::class);
        $mockActionHandler->expects($this->once())
            ->method('handleRemainingPlayersAfterAllIn')
            ->with($this->game);

        $actionHandlerProp = $reflection->getProperty('actionHandler');
        $actionHandlerProp->setAccessible(true);
        $actionHandlerProp->setValue($this->game, $mockActionHandler);

        // Execute the handleAction method with 'all-in' action
        $this->game->handleAction($mockPlayer, 'all-in');

        // Verify that the allInOccurred flag is set to true
        $this->assertTrue($this->game->hasAllInOccurred());
    }
    public function testHandleRemainingPlayersAfterAllIn(): void
    {
        $mockPlayerAction = $this->createMock(PlayerActionHandler::class);

        // Set the expectation that handleRemainingPlayersAfterAllIn is called once
        $mockPlayerAction->expects($this->once())
            ->method('handleRemainingPlayersAfterAllIn')
            ->with($this->game);

        // Call the method under test
        $this->game->handleRemainingPlayersAfterAllIn($mockPlayerAction);
    }

}
