<?php

namespace App\Tests\CardGame;

use App\CardGame\PlayerManagerJson;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;

class PlayerManagerJsonTest extends TestCase
{
    /** @var PlayerManagerJson */
    private $playerManager;

    protected function setUp(): void
    {
        $this->playerManager = new PlayerManagerJson();
    }

    public function testSetChipsSuccess(): void
    {
        $gameMock = $this->createMock(TexasHoldemGame::class);
        $playerMock = $this->createMock(Player::class);

        $gameMock->expects($this->once())
            ->method('getPlayers')
            ->willReturn([$playerMock]);

        $playerMock->expects($this->once())
            ->method('setChips')
            ->with(1500);

        $result = $this->playerManager->setChips($gameMock, 0, 1500);

        $this->assertIsArray($result);
        $this->assertEquals('Player chips set successfully', $result['message']);
    }

    public function testSetChipsInvalidPlayerIndex(): void
    {
        $gameMock = $this->createMock(TexasHoldemGame::class);

        $gameMock->expects($this->once())
            ->method('getPlayers')
            ->willReturn([]);

        $result = $this->playerManager->setChips($gameMock, 0, 1500);

        $this->assertIsArray($result);
        $this->assertEquals('Invalid player index', $result['error']);
    }

    public function testGetPlayerCardsSuccess(): void
    {
        $playerName = 'You';

        $gameMock = $this->createMock(TexasHoldemGame::class);
        $playerMock = $this->createMock(Player::class);

        $gameMock->expects($this->once())
            ->method('getPlayers')
            ->willReturn([$playerMock]);

        $playerMock->expects($this->once())
            ->method('getName')
            ->willReturn($playerName);

        $playerMock->expects($this->once())
            ->method('getHand')
            ->willReturn([
                $this->createConfiguredMock(CardGraphic::class, ['getAsString' => 'Card1']),
                $this->createConfiguredMock(CardGraphic::class, ['getAsString' => 'Card2']),
            ]);

        $result = $this->playerManager->getPlayerCards($gameMock, $playerName);

        $this->assertIsArray($result);
        $this->assertEquals($playerName, $result['player_name']);
        $this->assertEquals(['Card1', 'Card2'], $result['cards']);
    }

    public function testGetPlayerCardsPlayerNotFound(): void
    {
        $playerName = 'NonExistentPlayer';

        $gameMock = $this->createMock(TexasHoldemGame::class);

        $gameMock->expects($this->once())
            ->method('getPlayers')
            ->willReturn([]);

        $result = $this->playerManager->getPlayerCards($gameMock, $playerName);

        $this->assertIsArray($result);
        $this->assertEquals("Player with name NonExistentPlayer not found", $result['error']);
    }
}
