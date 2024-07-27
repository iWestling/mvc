<?php

namespace App\Tests\CardGame;

use App\CardGame\CardHand;
use App\CardGame\GameLogger;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;

class GameLoggerTest extends TestCase
{
    /** @var MockObject&SessionInterface */
    private MockObject $session;

    /** @var MockObject&CardHand */
    private MockObject $playerHand;

    /** @var MockObject&CardHand */
    private MockObject $dealerHand;

    private GameLogger $gameLogger;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->playerHand = $this->createMock(CardHand::class);
        $this->dealerHand = $this->createMock(CardHand::class);
        $this->gameLogger = new GameLogger();
    }

    public function testLogGameStart(): void
    {
        $this->session->method('get')
            ->will($this->returnValueMap([
                ['playerBet', null, 10],
                ['gameLog', null, ''],
            ]));

        $this->playerHand->method('getCards')
            ->willReturn([
                $this->createConfiguredMock(CardGraphic::class, [
                    'getCardName' => 'ace',
                    'getSuit' => 'hearts',
                ]),
                $this->createConfiguredMock(CardGraphic::class, [
                    'getCardName' => 'king',
                    'getSuit' => 'diamonds',
                ]),
            ]);

        $this->dealerHand->method('getCards')
            ->willReturn([
                $this->createConfiguredMock(CardGraphic::class, [
                    'getCardName' => 'queen',
                    'getSuit' => 'clubs',
                ]),
                $this->createConfiguredMock(CardGraphic::class, [
                    'getCardName' => 'jack',
                    'getSuit' => 'spades',
                ]),
            ]);

        $gameLog = $this->gameLogger->logGameStart($this->session, $this->playerHand, $this->dealerHand);
        $this->assertStringContainsString('Started new round', $gameLog);
        $this->assertStringContainsString('Registered Bet as 10', $gameLog);
    }

    public function testUpdateGameLog(): void
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with('gameLog')
            ->willReturn('Initial log.');

        $this->session->expects($this->once())
            ->method('set')
            ->with('gameLog', 'Initial log.Updated log.');

        $this->gameLogger->updateGameLog($this->session, 'Updated log.');
    }
}
