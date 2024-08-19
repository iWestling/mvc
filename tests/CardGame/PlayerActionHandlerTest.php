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

    public function testRotateRoles(): void
    {
        $dealerIndex = 0;
        $this->actionHandler->rotateRoles($dealerIndex, 3);
        $this->assertEquals(1, $dealerIndex);

        $this->actionHandler->rotateRoles($dealerIndex, 3);
        $this->assertEquals(2, $dealerIndex);

        $this->actionHandler->rotateRoles($dealerIndex, 3);
        $this->assertEquals(0, $dealerIndex);
    }

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

        // No assertion here because the method handles game advancement internally.
        // The primary goal is to ensure no errors occur during execution.
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

        // Again, no direct assertion is needed since we're testing method execution without errors.
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

}
