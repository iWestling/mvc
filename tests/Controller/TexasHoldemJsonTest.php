<?php

namespace App\Tests\Controller;

use App\Controller\TexasHoldemJson;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\CardGraphic;
use App\CardGame\CommunityCardManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class TexasHoldemJsonTest extends WebTestCase
{
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
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->controller = new TexasHoldemJson();
    }

    public function testApiPage(): void
    {
        // Mock the template rendering with 'texas/api.html.twig'
        $controller = $this->getMockBuilder(TexasHoldemJson::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/api.html.twig')
            ->willReturn(new Response());

        // Call the apiPage method and check the response
        $response = $controller->apiPage();
        $this->assertInstanceOf(Response::class, $response);
    }
    public function testStartNewGame(): void
    {
        $request = new Request([], [
            'chips' => 1000,
            'level1' => 'normal',
            'level2' => 'intelligent'
        ]);

        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('game', $this->isInstanceOf(TexasHoldemGame::class));

        $response = $this->controller->startNewGame($request, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
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
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
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

        $response = $this->controller->getGameState($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('players', $data);
        $this->assertIsArray($data['players']);
        $this->assertEquals('You', $data['players'][0]['name']);
    }

    public function testStartNewRound(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $game->expects($this->once())->method('startNewRound');
        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('game', $game);

        $response = $this->controller->startNewRound($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('New round started.', $data['message']);
    }

    public function testResetGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('remove')
            ->with('game');

        $response = $this->controller->resetGame($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Game reset successfully.', $data['message']);
    }

    public function testSetChips(): void
    {
        $game = new TexasHoldemGame();
        $player = new Player('You', 1000, 'intelligent');
        $game->addPlayer($player);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $request = new Request([], ['chips' => 1500]);

        $response = $this->controller->setChips($request, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertEquals(1500, $player->getChips());
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Your chips set successfully.', $data['message']);
    }

    public function testSetChipsCompOne(): void
    {
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', 1000, 'intelligent'));
        $game->addPlayer(new Player('Computer 1', 1000, 'normal'));

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $request = new Request([], ['chips' => 2000]);

        $response = $this->controller->setChipsCompOne($request, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertEquals(2000, $game->getPlayers()[1]->getChips());
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Computer 1 chips set successfully.', $data['message']);
    }

    public function testSetChipsCompTwo(): void
    {
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', 1000, 'intelligent'));
        $game->addPlayer(new Player('Computer 1', 1000, 'normal'));
        $game->addPlayer(new Player('Computer 2', 1000, 'intelligent'));

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $request = new Request([], ['chips' => 3000]);

        $response = $this->controller->setChipsCompTwo($request, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertEquals(3000, $game->getPlayers()[2]->getChips());
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Computer 2 chips set successfully.', $data['message']);
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
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
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

        $response = $this->controller->getCommunityCards($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('community_cards', $data);
        $this->assertEquals(['mock_card_1', 'mock_card_2'], $data['community_cards']);
    }

    public function testGetPlayerCardsWithoutGame(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        $response = $this->controller->getPlayerCards($this->sessionMock, 'PlayerName');
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No game found. Start a new game first.', $data['error']);
    }

    public function testGetPlayerCards(): void
    {
        $game = $this->createMock(TexasHoldemGame::class);
        $player = $this->createMock(Player::class);

        $mockCard1 = $this->createMock(CardGraphic::class);
        $mockCard1->method('getAsString')->willReturn('mock_card_1');

        $mockCard2 = $this->createMock(CardGraphic::class);
        $mockCard2->method('getAsString')->willReturn('mock_card_2');

        $player->method('getHand')->willReturn([$mockCard1, $mockCard2]);
        $player->method('getName')->willReturn('PlayerName');

        $game->method('getPlayers')->willReturn([$player]);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($game);

        $response = $this->controller->getPlayerCards($this->sessionMock, 'PlayerName');
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'JSON response should decode to an array');
        $this->assertArrayHasKey('player_name', $data);
        $this->assertArrayHasKey('cards', $data);
        $this->assertEquals('PlayerName', $data['player_name']);
        $this->assertEquals(['mock_card_1', 'mock_card_2'], $data['cards']);
    }
}
