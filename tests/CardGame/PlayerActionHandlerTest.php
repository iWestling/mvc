<?php

namespace App\Tests\CardGame;

use App\CardGame\PlayerActionHandler;
use App\CardGame\Player;
use App\CardGame\PotManager;
use App\CardGame\TexasHoldemGame;
use App\CardGame\CommunityCardManager;
use App\CardGame\Deck;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class PlayerActionHandlerTest extends TestCase
{
    private PotManager $potManager;
    private PlayerActionHandler $actionHandler;
    private TexasHoldemGame $game;

    protected function setUp(): void
    {
        $this->potManager = new PotManager();
        $this->actionHandler = new PlayerActionHandler($this->potManager);
        $this->game = new TexasHoldemGame();
    }

    // public function testRotateRoles(): void
    // {
    //     $dealerIndex = 0;
    //     $this->actionHandler->rotateRoles($dealerIndex, 3);
    //     $this->assertEquals(1, $dealerIndex);

    //     $this->actionHandler->rotateRoles($dealerIndex, 3);
    //     $this->assertEquals(2, $dealerIndex);

    //     $this->actionHandler->rotateRoles($dealerIndex, 3);
    //     $this->assertEquals(0, $dealerIndex);
    // }

    public function testProcessActionsInOrder(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $communityCardManager = new CommunityCardManager(new Deck());

        // Use an anonymous function as the callable
        $handleAction = function ($player, $action) {
            $this->assertNotNull($player);
            $this->assertNotNull($action);
        };

        $this->actionHandler->processActionsInOrder(
            [$player1, $player2],
            'check',
            0,
            $communityCardManager,
            $handleAction
        );
    }

    public function testProcessPlayerActionFold(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $player1->fold();

        $this->actionHandler->processPlayerAction($player1, $this->game, 'fold', 0);

        $this->assertTrue($player1->isFolded());
        $this->assertFalse($this->game->isGameOver()); // The game should not be over since another player remains
    }

    public function testProcessPlayerActionRaise(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->actionHandler->processPlayerAction($player1, $this->game, 'raise', 100);

        $this->assertEquals(100, $player1->getCurrentBet());
    }

    public function testHandleRemainingPlayersAfterAllIn(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $player1->setCurrentBet(500);
        $this->potManager->updateCurrentBet(500);

        $this->actionHandler->handleRemainingPlayersAfterAllIn($this->game);

        $this->assertEquals(500, $this->potManager->getCurrentBet());
    }

    public function testProcessNextPlayerActions(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->potManager->updateCurrentBet(100);

        $this->actionHandler->processNextPlayerActions($this->game);

        $this->assertNotNull($this->game->getPlayersInOrder());
    }

    public function testProcessRaiseResponses(): void
    {
        $player1 = new Player('Player 1', 1000, 'normal');
        $player2 = new Player('Player 2', 1000, 'normal');
        $this->game->addPlayer($player1);
        $this->game->addPlayer($player2);

        $this->potManager->updateCurrentBet(200);

        $this->actionHandler->processRaiseResponses($this->game);

        $this->assertNotNull($this->game->getPlayers());
    }

    public function testNormalizeActionForAllIn(): void
    {
        $player = new Player('Player 1', 100, 'normal');
        $normalizedAction = $this->invokeMethod($this->actionHandler, 'normalizeAction', [$player, 'raise', 100]);

        $this->assertEquals('all-in', $normalizedAction);
    }

    /**
     * Helper method to invoke private methods for testing purposes.
     *
     * @param object $object The object on which to invoke the method.
     * @param string $methodName The name of the method to invoke.
     * @param array<mixed> $parameters Parameters to pass to the method.
     * @return mixed The result of the invoked method.
     */
    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    public function testProcessPlayerActionOnlyOneActivePlayer(): void
    {
        $player1 = $this->createMock(Player::class);
        $player1->expects($this->once())->method('isFolded')->willReturn(false);

        $gameMock = $this->createMock(TexasHoldemGame::class);

        $gameMock->expects($this->once())->method('countActivePlayers')->willReturn(1);
        $gameMock->expects($this->once())->method('determineWinner');
        $gameMock->expects($this->once())->method('setGameOver')->with(true);

        $this->actionHandler->processPlayerAction($player1, $gameMock, 'fold', 0);
    }

    public function testProcessPlayerActionCallsProcessNextPlayerActions(): void
    {
        // Create player mocks
        $player1 = $this->createMock(Player::class);
        $player1->method('isFolded')->willReturn(false);

        $player2 = $this->createMock(Player::class);
        $player2->method('isFolded')->willReturn(false);

        $gameMock = $this->createMock(TexasHoldemGame::class);

        $gameMock->expects($this->once())->method('countActivePlayers')->willReturn(2);

        $actionHandlerMock = $this->getMockBuilder(PlayerActionHandler::class)
            ->setConstructorArgs([$this->potManager])
            ->onlyMethods(['processNextPlayerActions'])
            ->getMock();

        $actionHandlerMock->expects($this->once())
            ->method('processNextPlayerActions')
            ->with($gameMock);

        // Simulate player folding action
        $actionHandlerMock->processPlayerAction($player1, $gameMock, 'fold', 0);
    }



    public function testProcessNextPlayerActionsOnlyOneActivePlayer(): void
    {
        $player1 = $this->createMock(Player::class);
        $player1->expects($this->once())->method('isFolded')->willReturn(false);
        $player1->expects($this->once())->method('getChips')->willReturn(100);

        $gameMock = $this->createMock(TexasHoldemGame::class);

        $gameMock->expects($this->once())->method('getPlayersInOrder')->willReturn([$player1]);

        $gameMock->expects($this->once())->method('countActivePlayers')->willReturn(1);
        $gameMock->expects($this->once())->method('determineWinner');
        $gameMock->expects($this->once())->method('setGameOver')->with(true);

        // Simulate processing next player actions
        $this->actionHandler->processNextPlayerActions($gameMock);
    }

    // public function testHandleRemainingPlayersAfterAllInCallsHandleActionWithCorrectCallAmount(): void
    // {
    //     $player1 = $this->createMock(Player::class);

    //     // Ensure the player is not folded and has chips
    //     $player1->expects($this->once())->method('isFolded')->willReturn(false);

    //     // Allow getChips to be called at least once since it may be called multiple times in the logic
    //     $player1->expects($this->atLeastOnce())->method('getChips')->willReturn(500);

    //     // Allow getCurrentBet to be called at least once
    //     $player1->expects($this->atLeastOnce())->method('getCurrentBet')->willReturn(200);

    //     // Ensure the player decides to call
    //     $player1->expects($this->once())->method('makeDecision')->willReturn('call');

    //     // Create a mock for TexasHoldemGame
    //     $gameMock = $this->createMock(TexasHoldemGame::class);

    //     // Mock game behavior to return players in order
    //     $gameMock->expects($this->once())->method('getPlayersInOrder')->willReturn([$player1]);

    //     // Set expectation for handleAction with the correct call amount
    //     $gameMock->expects($this->once())
    //              ->method('handleAction')
    //              ->with($player1, 'call', 300); // Expected call amount should be 300

    //     // Set the current bet to 500
    //     $this->potManager->updateCurrentBet(500);

    //     // Simulate handling remaining players after All-In
    //     $this->actionHandler->handleRemainingPlayersAfterAllIn($gameMock);
    // }


    public function testProcessActionsInOrderSkipsFoldedPlayer(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        // Simulate player1 being folded
        $player1->expects($this->once())->method('isFolded')->willReturn(true);

        // Ensure player2 is not folded
        $player2->expects($this->once())->method('isFolded')->willReturn(false);

        $communityCardManager = $this->createMock(CommunityCardManager::class);

        // Mock the action handler function
        $handleActionMock = function ($player) use ($player2) {
            // Ensure only player2 is processed
            $this->assertSame($player2, $player);
        };

        // Process actions in order with player1 folded
        $this->actionHandler->processActionsInOrder(
            [$player1, $player2],
            'check',
            0,
            $communityCardManager,
            $handleActionMock
        );
    }

    public function testProcessActionsInOrderHandlesHumanPlayer(): void
    {
        $humanPlayer = $this->createMock(Player::class);
        $computerPlayer = $this->createMock(Player::class);

        $humanPlayer->expects($this->once())->method('isFolded')->willReturn(false);
        $humanPlayer->expects($this->once())->method('getName')->willReturn('You');

        $computerPlayer->expects($this->once())->method('isFolded')->willReturn(false);
        $computerPlayer->expects($this->once())->method('getName')->willReturn('Computer');

        $communityCardManager = $this->createMock(CommunityCardManager::class);

        $handleAction = function ($player, $action, $raiseAmount = 0) use ($humanPlayer) {
            if ($player === $humanPlayer) {
                $this->assertEquals('raise', $action);
                $this->assertEquals(100, $raiseAmount);
            }
        };

        // Process actions in order with human player and computer player
        $this->actionHandler->processActionsInOrder(
            [$humanPlayer, $computerPlayer],
            'raise',
            100,
            $communityCardManager,
            $handleAction
        );
    }

}
