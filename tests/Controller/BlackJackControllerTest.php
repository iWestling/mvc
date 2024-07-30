<?php

namespace App\Tests\Controller;

use App\Controller\BlackJackController;
use App\CardGame\GameService;
use App\CardGame\GameLogger;
use App\CardGame\CardHand;
use App\CardGame\CardGraphic;
use App\CardGame\GameDataService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PHPUnit\Framework\MockObject\MockObject;

class BlackJackControllerTest extends WebTestCase
{
    /**
     * @var MockObject&GameService
     */
    private $gameServiceMock;

    /**
     * @var MockObject&GameLogger
     */
    private $gameLoggerMock;

    /**
     * @var MockObject&GameDataService
     */
    private $gameDataServiceMock;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->gameServiceMock = $this->createMock(GameService::class);
        $this->gameLoggerMock = $this->createMock(GameLogger::class);
        $this->gameDataServiceMock = $this->createMock(GameDataService::class);
    }
    public function testGame(): void
    {
        $this->gameServiceMock->expects($this->once())
            ->method('initializeGame')
            ->with($this->isInstanceOf(SessionInterface::class))
            ->willReturnCallback(function (SessionInterface $session) {
                $session->clear();
                $session->set('playerMoney', 100);
                $session->set('dealerMoney', 100);
            });

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('clear');

        // Use callback to verify the set calls
        $session->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($name, $value) {
                if ($name === 'playerMoney') {
                    $this->assertEquals(100, $value);
                } elseif ($name === 'dealerMoney') {
                    $this->assertEquals(100, $value);
                }
            });

        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/home.html.twig')
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->game($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }


    public function testInit(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->exactly(2))->method('get')->willReturn(100);

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/init.html.twig', ['playerMoney' => 100, 'dealerMoney' => 100])
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->init($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInitCallback(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['redirectToRoute', 'addFlash'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $request = new Request([], ['playerbet' => 50]);

        $this->gameServiceMock->expects($this->once())
            ->method('initGame')
            ->with($session, 50)
            ->willReturn(true);

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('game_play')
            ->willReturn($this->createMock(RedirectResponse::class));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->initCallback($request, $session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testInitCallbackFailed(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['redirectToRoute', 'addFlash'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $request = new Request([], ['playerbet' => 50]);

        $this->gameServiceMock->expects($this->once())
            ->method('initGame')
            ->with($session, 50)
            ->willReturn(false);

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('error', 'Failed to deal cards.');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('game_init')
            ->willReturn($this->createMock(RedirectResponse::class));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->initCallback($request, $session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testPlay(): void
    {
        $this->gameServiceMock->expects($this->once())
            ->method('isValidBet')
            ->with($this->isInstanceOf(SessionInterface::class))
            ->willReturn(true);

        $this->gameServiceMock->expects($this->once())
            ->method('adjustMoneyForBet')
            ->with($this->isInstanceOf(SessionInterface::class));

        $this->gameLoggerMock->expects($this->once())
            ->method('logGameStart')
            ->with(
                $this->isInstanceOf(SessionInterface::class),
                $this->isInstanceOf(CardHand::class),
                $this->isInstanceOf(CardHand::class)
            );

        $session = $this->createMock(SessionInterface::class);
        $playerHand = $this->createMock(CardHand::class);
        $dealerHand = $this->createMock(CardHand::class);
        $card = $this->createMock(CardGraphic::class);

        $playerHand->method('getCards')->willReturn([$card]);
        $dealerHand->method('getCards')->willReturn([$card, $card]);

        $playerHand->method('calculateTotal')->willReturn(['low' => 10, 'high' => 20]);
        $dealerHand->method('calculateTotalDealer')->willReturn(['low' => 5, 'high' => 15]);

        $session->method('get')
            ->willReturnMap([
                ['playerHand', null, $playerHand],
                ['dealerHand', null, $dealerHand],
                ['playerMoney', null, 100],
                ['playerBet', null, 50],
                ['dealerMoney', null, 100],
                ['gameLog', null, ''],
            ]);

        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/play.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->play($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStand(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $playerHand = $this->createMock(CardHand::class);
        $dealerHand = $this->createMock(CardHand::class);
        $card = $this->createMock(CardGraphic::class);

        $dealerHand->method('getCards')->willReturn([$card, $card]);
        $playerHand->method('calculateTotal')->willReturn(['low' => 10, 'high' => 20]);
        $dealerHand->method('calculateTotal')->willReturn(['low' => 5, 'high' => 15]);

        $session->method('get')
            ->willReturnMap([
                ['playerHand', null, $playerHand],
                ['dealerHand', null, $dealerHand],
                ['playerBet', null, 50],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
            ]);

        $this->gameLoggerMock->expects($this->once())
            ->method('updateGameLog')
            ->with($session, $this->stringContains('Player stands.'));

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/play.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->stand($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testHit(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render', 'redirectToRoute', 'addFlash'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $playerHand = $this->createMock(CardHand::class);
        $dealerHand = $this->createMock(CardHand::class);
        $deck = [$this->createMock(CardGraphic::class)];
        $card1 = $this->createMock(CardGraphic::class);
        $card2 = $this->createMock(CardGraphic::class);

        $playerHand->expects($this->once())->method('addCard')->with($card1);
        $playerHand->method('calculateTotal')->willReturn(['low' => 10, 'high' => 20]);
        $dealerHand->method('calculateTotalDealer')->willReturn(['low' => 5, 'high' => 15]);
        $dealerHand->method('getCards')->willReturn([$card1, $card2]);

        $session->method('get')
            ->willReturnMap([
                ['playerBet', null, 50],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
                ['playerHand', null, $playerHand],
                ['dealerHand', null, $dealerHand],
                ['deck', null, $deck],
            ]);

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/play.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $controller->expects($this->never())
            ->method('redirectToRoute');

        $controller->expects($this->never())
            ->method('addFlash');

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->hit($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testDealerHit(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render', 'redirectToRoute', 'addFlash'])
            ->getMock();

        $session = $this->createMock(SessionInterface::class);
        $playerHand = $this->createMock(CardHand::class);
        $dealerHand = $this->createMock(CardHand::class);
        $deck = [$this->createMock(CardGraphic::class)];
        $card = $this->createMock(CardGraphic::class);

        $dealerHand->method('getCards')->willReturn([$card, $card]);
        $dealerHand->expects($this->once())->method('addCard')->with($card);
        $playerHand->method('calculateTotal')->willReturn(['low' => 10, 'high' => 20]);
        $dealerHand->method('calculateTotal')->willReturn(['low' => 5, 'high' => 15]);

        $session->method('get')
            ->willReturnMap([
                ['playerBet', null, 50],
                ['playerMoney', null, 100],
                ['dealerMoney', null, 100],
                ['playerHand', null, $playerHand],
                ['dealerHand', null, $dealerHand],
                ['deck', null, $deck],
            ]);

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/play.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $controller->expects($this->never())
            ->method('redirectToRoute');

        $controller->expects($this->never())
            ->method('addFlash');

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->dealerHit($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testEndResult(): void
    {
        $this->gameServiceMock = $this->createMock(GameService::class);
        $this->gameLoggerMock = $this->createMock(GameLogger::class);

        $this->gameLoggerMock->expects($this->once())
            ->method('updateGameLog')
            ->with($this->isInstanceOf(SessionInterface::class), $this->stringContains('Round ended.'));

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->will($this->returnValueMap([
            ['playerBet', null, 10],
            ['playerMoney', null, 100],
            ['dealerMoney', null, 100],
            ['playerHand', null, $this->createCardHandMock()],
            ['dealerHand', null, $this->createCardHandMock()],
            ['gameLog', null, '']
        ]));

        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/play.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->endResult($session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    private function createCardHandMock(): MockObject
    {
        $cardHandMock = $this->getMockBuilder(CardHand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cardHandMock->method('calculateTotal')->willReturn(['low' => 10, 'high' => 20]);
        $cardHandMock->method('getCards')->willReturn([
            $this->createCardGraphicMock(),
            $this->createCardGraphicMock()
        ]);

        return $cardHandMock;
    }

    private function createCardGraphicMock(): MockObject
    {
        $cardGraphicMock = $this->getMockBuilder(CardGraphic::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cardGraphicMock->method('getValue')->willReturn(10);
        $cardGraphicMock->method('getAsString')->willReturn('10 of Hearts');
        $cardGraphicMock->method('getCardName')->willReturn('10');
        $cardGraphicMock->method('getSuit')->willReturn('Hearts');
        $cardGraphicMock->method('getUnturned')->willReturn('card_back.png');

        return $cardGraphicMock;
    }

    public function testDoc(): void
    {
        $controller = $this->getMockBuilder(BlackJackController::class)
            ->setConstructorArgs([$this->gameServiceMock, $this->gameLoggerMock, $this->gameDataServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('blackjack/doc.html.twig')
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);

        $controller->setContainer($container);

        $response = $controller->doc();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}
