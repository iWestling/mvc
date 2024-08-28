<?php

namespace App\Tests\Controller;

use App\Controller\TexasHoldemJson;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\CardGraphic;
use App\CardGame\CommunityCardManager;
use App\CardGame\GameManagerJson;
use App\CardGame\PlayerManagerJson;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class TexasHoldemJsonTest extends WebTestCase
{
    /**
     * @var MockObject&GameManagerJson
     */
    private $gameManagerMock;

    /**
     * @var MockObject&PlayerManagerJson
     */
    private $playerManagerMock;

    /**
     * @var MockObject&SessionInterface
     */
    private $sessionMock;

    /**
     * @var TexasHoldemJson
     */
    private $controller;

    protected function setUp(): void
    {
        $this->gameManagerMock = $this->createMock(GameManagerJson::class);
        $this->playerManagerMock = $this->createMock(PlayerManagerJson::class);
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->controller = new TexasHoldemJson($this->gameManagerMock, $this->playerManagerMock);
    }

    public function testStartNewGame(): void
    {
        $request = new Request([], [
            'chips' => 1000,
            'level1' => 'normal',
            'level2' => 'intelligent'
        ]);

        $gameMock = $this->createMock(TexasHoldemGame::class);

        $this->gameManagerMock->expects($this->once())
            ->method('startNewGame')
            ->with(1000, 'normal', 'intelligent')
            ->willReturn($gameMock);

        /** @scrutinizer ignore-call */ /** @scrutinizer ignore-deprecated */ $this->sessionMock->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['game', $gameMock],
                ['current_action_index', 0]
            );

        $response = $this->controller->startNewGame($request, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        // Decode JSON to check the response data
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('New game started successfully.', $data['message']);
    }

    public function testGetGameStateWithoutGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        $response = $this->controller->getGameState($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No game found. Start a new game first.', $data['error']);
    }

    public function testGetGameStateWithGame(): void
    {
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', 1000, 'intelligent'));
        $game->addPlayer(new Player('Computer 1', 1000, 'normal'));

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $this->gameManagerMock->expects($this->once())
            ->method('getGameState')
            ->with($game)
            ->willReturn([
                'players' => [],
                'community_cards' => [],
                'pot' => 0,
            ]);

        $response = $this->controller->getGameState($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('players', $data);
        $this->assertArrayHasKey('community_cards', $data);
        $this->assertArrayHasKey('pot', $data);
    }

    public function testResetGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('remove')
            ->with('game');

        $response = $this->controller->resetGame($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Game reset successfully.', $data['message']);
    }

    public function testSetChipsForPlayer(): void
    {
        $game = new TexasHoldemGame();
        $player = new Player('You', 1000, 'intelligent');
        $game->addPlayer($player);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $this->playerManagerMock->expects($this->once())
            ->method('setChips')
            ->with($game, 0, 1500)
            ->willReturn(['message' => 'Player chips set successfully.']);

        $request = new Request([], ['chips' => 1500]);
        $response = $this->controller->setChips($request, $this->sessionMock, 0);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Player chips set successfully.', $data['message']);
    }

    public function testSetChipsNoGameFound(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        $request = new Request([], ['chips' => 1000]);
        $response = $this->controller->setChips($request, $this->sessionMock, 0);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No game found. Start a new game first.', $data['error']);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetCommunityCardsWithoutGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        $response = $this->controller->getCommunityCards($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No game found. Start a new game first.', $data['error']);
    }

    public function testGetCommunityCards(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $communityCardManager = $this->createMock(CommunityCardManager::class);

        $mockCard1 = $this->createMock(CardGraphic::class);
        $mockCard1->method('getAsString')->willReturn('mock_card_1');

        $mockCard2 = $this->createMock(CardGraphic::class);
        $mockCard2->method('getAsString')->willReturn('mock_card_2');

        $communityCardManager->method('getCommunityCards')->willReturn([$mockCard1, $mockCard2]);

        $game->method('getCommunityCardManager')->willReturn($communityCardManager);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $this->gameManagerMock->expects($this->once())
            ->method('getCommunityCards')
            ->with($game)
            ->willReturn(['mock_card_1', 'mock_card_2']);

        $response = $this->controller->getCommunityCards($this->sessionMock);
        $content = $response->getContent();

        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertNotFalse($data, 'json_decode should not return false'); // Ensure decoding was successful
        $this->assertIsArray($data, 'Decoded data should be an array'); // Ensure the decoded data is an array
        $this->assertArrayHasKey('community_cards', $data);
    }

    public function testGetPlayerCardsWithoutGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        $response = $this->controller->getPlayerCards('PlayerName', $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No game found. Start a new game first.', $data['error']);
    }
    public function testGetPlayerCardsPlayerNotFound(): void
    {
        $playerName = 'NonExistentPlayer';
        $game = $this->createMock(TexasHoldemGame::class);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $game->expects($this->once())
            ->method('getPlayers')
            ->willReturn([]);

        $response = $this->controller->getPlayerCards($playerName, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string'); // Ensure content is a string

        $data = json_decode($content, true);
        $this->assertNotFalse($data, 'json_decode should not return false'); // Ensure decoding was successful
        $this->assertIsArray($data, 'Decoded data should be an array'); // Ensure the decoded data is an array
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals("Player with name $playerName not found", $data['error']);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetPlayerCardsSuccess(): void
    {
        $playerName = 'You';
        $game = $this->createMock(TexasHoldemGame::class);
        $player = $this->createMock(Player::class);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $game->expects($this->once())
            ->method('getPlayers')
            ->willReturn([$player]);

        $player->expects($this->once())
            ->method('getName')
            ->willReturn($playerName);

        $this->playerManagerMock->expects($this->once())
            ->method('getPlayerCards')
            ->with($game, $playerName)
            ->willReturn([
                'player_name' => $playerName,
                'cards' => ['Card1', 'Card2']
            ]);

        $response = $this->controller->getPlayerCards($playerName, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('player_name', $data);
        $this->assertEquals($playerName, $data['player_name']);
        $this->assertArrayHasKey('cards', $data);
        $this->assertEquals(['Card1', 'Card2'], $data['cards']);
    }

}
