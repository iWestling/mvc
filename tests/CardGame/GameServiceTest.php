<?php

namespace App\Tests\CardGame;

use App\CardGame\GameService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method mixed get(string $name, mixed $default = null)
 * @method void method(string $name)
 * @method void willReturnMap(array $map)
 */
class GameServiceTest extends TestCase
{
    /** @var MockObject&SessionInterface */
    private MockObject $session;

    private GameService $gameService;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->gameService = new GameService();
    }

    public function testInitializeGame(): void
    {
        $this->session->expects($this->once())->method('clear');
    
        $this->session->expects($this->at(0))
            ->method('set')
            ->with('playerMoney', 100);
    
        $this->session->expects($this->at(1))
            ->method('set')
            ->with('dealerMoney', 100);
    
        $this->gameService->initializeGame($this->session);
    }


    public function testInitGameSuccess(): void
    {
        $this->session->expects($this->exactly(4))
            ->method('set');

        $result = $this->gameService->initGame($this->session, 10);
        $this->assertTrue($result);
    }

    public function testAdjustMoneyForBet(): void
    {
        $this->session->method('get')
            ->willReturnMap([
                ['playerBet', null, 10],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
            ]);
    
        $this->session->expects($this->at(0))
            ->method('set')
            ->with('playerMoney', 90);
    
        $this->session->expects($this->at(1))
            ->method('set')
            ->with('dealerMoney', 90);
    
        $this->gameService->adjustMoneyForBet($this->session);
    }

    public function testIsValidBet(): void
    {
        $this->session->method('get')
            ->willReturnMap([
                ['playerBet', null, 10],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
            ]);

        $this->assertTrue($this->gameService->isValidBet($this->session));
    }

    public function testIsInvalidBet(): void
    {
        $this->session->method('get')
            ->willReturnMap([
                ['playerBet', null, 110],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
            ]);

        $this->assertFalse($this->gameService->isValidBet($this->session));
    }
}
