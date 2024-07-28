<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\BlackJackJson;
use App\CardGame\CardHand;
use App\CardGame\CardGraphic;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method mixed get(string $name, mixed $default = null)
 * @method void method(string $name)
 * @method void willReturnMap(array $map)
 */
class BlackJackJsonTest extends WebTestCase
{
    /**
     * @var MockObject&SessionInterface
     */
    private $sessionMock;

    /**
     * @var BlackJackJson
     */
    private $controller;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->controller = new BlackJackJson();
    }

    public function testPlayWithoutGameStarted(): void
    {
        $this->sessionMock->method('get')
            ->willReturnMap([
                ['playerBet', null, 50],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
                ['playerHand', null, null],
                ['dealerHand', null, null],
                ['gameLog', null, '']
            ]);

        $response = $this->controller->play($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Please start a game.', $data['message']);
    }

    public function testPlayWithGameInProgress(): void
    {
        $card1 = $this->createCardGraphicMock('5 of Hearts', 5, 'Hearts');
        $card2 = $this->createCardGraphicMock('10 of Diamonds', 10, 'Diamonds');
        $playerHand = $this->createCardHandMock([$card1, $card2], ['low' => 15, 'high' => 15]);
        $dealerCard = $this->createCardGraphicMock('7 of Clubs', 7, 'Clubs', 'card_back.png');
        $dealerHand = $this->createCardHandMock([$dealerCard], ['low' => 7, 'high' => 7]);

        $this->sessionMock->method('get')
            ->willReturnMap([
                ['playerBet', null, 50],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
                ['playerHand', null, $playerHand],
                ['dealerHand', null, $dealerHand],
                ['gameLog', null, 'Player hits and gets 5 of Hearts.'],
            ]);

        $response = $this->controller->play($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('playerHand', $data);
        $this->assertArrayHasKey('dealerHand', $data);
        $this->assertArrayHasKey('dealerUnturned', $data);
        $this->assertArrayHasKey('playerMoney', $data);
        $this->assertArrayHasKey('playerBet', $data);
        $this->assertArrayHasKey('dealerMoney', $data);
        $this->assertArrayHasKey('playerTotalLow', $data);
        $this->assertArrayHasKey('playerTotalHigh', $data);
        $this->assertArrayHasKey('dealerTotalLow', $data);
        $this->assertArrayHasKey('dealerTotalHigh', $data);
        $this->assertArrayHasKey('gameLog', $data);

        $this->assertEquals(['5 of Hearts', '10 of Diamonds'], $data['playerHand']);
        $this->assertEquals(['7 of Clubs'], $data['dealerHand']);
        $this->assertEquals('card_back.png', $data['dealerUnturned']);
        $this->assertEquals(100, $data['playerMoney']);
        $this->assertEquals(50, $data['playerBet']);
        $this->assertEquals(100, $data['dealerMoney']);
        $this->assertEquals(15, $data['playerTotalLow']);
        $this->assertEquals(15, $data['playerTotalHigh']);
        $this->assertEquals(7, $data['dealerTotalLow']);
        $this->assertEquals(7, $data['dealerTotalHigh']);
        $this->assertEquals('Player hits and gets 5 of Hearts.', $data['gameLog']);
    }

    /**
     * @param CardGraphic[] $cards
     * @param array<string, int> $totals
     * @return MockObject&CardHand
     */
    private function createCardHandMock(array $cards, array $totals): MockObject
    {
        $handMock = $this->createMock(CardHand::class);
        $handMock->method('getCards')->willReturn($cards);
        $handMock->method('calculateTotal')->willReturn($totals);
        $handMock->method('calculateTotalDealer')->willReturn($totals);
        return $handMock;
    }

    /**
     * @return MockObject&CardGraphic
     */
    private function createCardGraphicMock(string $asString, int $value, string $suit, string $unturned = 'card_back.png'): MockObject
    {
        $cardMock = $this->createMock(CardGraphic::class);
        $cardMock->method('getAsString')->willReturn($asString);
        $cardMock->method('getValue')->willReturn($value);
        $cardMock->method('getSuit')->willReturn($suit);
        $cardMock->method('getUnturned')->willReturn($unturned);
        return $cardMock;
    }
}
