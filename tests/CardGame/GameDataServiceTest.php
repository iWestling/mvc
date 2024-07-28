<?php

namespace App\Tests\CardGame;

use App\CardGame\GameDataService;
use App\CardGame\CardHand;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @method mixed get(string $name, mixed $default = null)
 * @method void method(string $name)
 * @method void willReturnMap(array $map)
 */
class GameDataServiceTest extends TestCase
{
    /**
     * @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var CardHand&\PHPUnit\Framework\MockObject\MockObject
     */
    private $playerHandMock;

    /**
     * @var CardHand&\PHPUnit\Framework\MockObject\MockObject
     */
    private $dealerHandMock;

    /**
     * @var CardGraphic&\PHPUnit\Framework\MockObject\MockObject
     */
    private $cardMock;

    /**
     * @var GameDataService
     */
    private $gameDataService;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->playerHandMock = $this->createMock(CardHand::class);
        $this->dealerHandMock = $this->createMock(CardHand::class);
        $this->cardMock = $this->createMock(CardGraphic::class);

        $this->gameDataService = new GameDataService();
    }

    public function testGetGameData(): void
    {
        // Mock session values
        $this->sessionMock->method('get')
            ->willReturnMap([
                ['playerMoney', null, 100],
                ['playerBet', null, 50],
                ['dealerMoney', null, 150],
                ['gameLog', null, 'Player hits and gets 5 of Hearts.'],
            ]);

        // Mock card behavior
        $this->cardMock->method('getAsString')->willReturn('5 of Hearts');
        $this->playerHandMock->method('getCards')->willReturn([$this->cardMock, $this->cardMock]);
        $this->dealerHandMock->method('getCards')->willReturn([$this->cardMock, $this->cardMock]);

        // Define player and dealer totals
        $playerTotals = ['low' => 5, 'high' => 15];
        $dealerTotals = ['low' => 7, 'high' => 17];
        $resultMessage = 'Player wins';

        // Call the method under test
        $gameData = $this->gameDataService->getGameData(
            $this->sessionMock,
            $this->playerHandMock,
            $this->dealerHandMock,
            $playerTotals,
            $dealerTotals,
            $resultMessage
        );

        // Assertions
        $this->assertIsArray($gameData);
        $this->assertEquals(['5 of Hearts', '5 of Hearts'], $gameData['playerHand']);
        $this->assertEquals(['5 of Hearts', '5 of Hearts'], $gameData['dealerHand']);
        $this->assertEquals(100, $gameData['playerMoney']);
        $this->assertEquals(50, $gameData['playerBet']);
        $this->assertEquals(150, $gameData['dealerMoney']);
        $this->assertEquals('5 of Hearts', $gameData['dealerUnturned']);
        $this->assertEquals(5, $gameData['playerTotalLow']);
        $this->assertEquals(15, $gameData['playerTotalHigh']);
        $this->assertEquals(7, $gameData['dealerTotalLow']);
        $this->assertEquals(17, $gameData['dealerTotalHigh']);
        $this->assertEquals('Player wins', $gameData['resultMessage']);
        $this->assertEquals('Player hits and gets 5 of Hearts.', $gameData['gameLog']);
    }
}
