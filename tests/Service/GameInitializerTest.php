<?php

namespace App\Tests\Service;

use App\Service\GameInitializer;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\PlayerActionInit;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

class GameInitializerTest extends TestCase
{
    /** @var GameInitializer */
    private $gameInitializer;

    /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    protected function setUp(): void
    {
        $this->gameInitializer = new GameInitializer();
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testInitializeGame(): void
    {
        $game = $this->gameInitializer->initializeGame(1000, 'normal', 'normal');

        $this->assertInstanceOf(TexasHoldemGame::class, $game);
        $this->assertCount(3, $game->getPlayers());

        $players = $game->getPlayers();
        $this->assertEquals('You', $players[0]->getName());
        $this->assertEquals('Computer 1', $players[1]->getName());
        $this->assertEquals('Computer 2', $players[2]->getName());
    }

    public function testSaveGameToSession(): void
    {
        $game = new TexasHoldemGame();

        // Expect session to set the 'game' and 'current_action_index'
        /** @scrutinizer ignore-deprecated */ $this->session->/** @scrutinizer ignore-call */ expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['game', $game],
                ['current_action_index', 0]
            );

        // Call the saveGameToSession method
        $this->gameInitializer->saveGameToSession($this->session, $game);
    }
}
