<?php

namespace App\Tests\Controller;

use App\Controller\CardGameController;
use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

class CardGameControllerTest extends WebTestCase
{
    /** @var MockObject&SessionInterface */
    private MockObject $sessionMock;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
    }

    public function testShowSession(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('all')
            ->willReturn(['foo' => 'bar']);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('card/session.html.twig', ['sessionData' => ['foo' => 'bar']])
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->showSession($this->sessionMock);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    // public function testDeleteSession(): void
    // {
    //     $this->sessionMock->expects($this->once())
    //         ->method('clear');

    //     $controller = $this->getMockBuilder(CardGameController::class)
    //         ->onlyMethods(['redirectToRoute'])
    //         ->getMock();

    //     $controller->expects($this->once())
    //         ->method('redirectToRoute')
    //         ->with('session_show')
    //         ->willReturn(new Response());

    //     $container = $this->createMock(ContainerInterface::class);
    //     $controller->setContainer($container);

    //     $response = $controller->deleteSession($this->sessionMock);

    //     $this->assertNotNull($response);
    //     $this->assertInstanceOf(Response::class, $response);
    // }

    // public function testShowDeck(): void
    // {
    //     $deckMock = $this->createMock(DeckOfCards::class);
    //     $deckMock->expects($this->once())
    //         ->method('getCards')
    //         ->willReturn([$this->createMock(Card::class)]);

    //     $controller = $this->getMockBuilder(CardGameController::class)
    //         ->onlyMethods(['render'])
    //         ->getMock();

    //     $controller->expects($this->once())
    //         ->method('render')
    //         ->with('card/deck.html.twig', $this->isType('array'))
    //         ->willReturn(new Response());

    //     $container = $this->createMock(ContainerInterface::class);
    //     $controller->setContainer($container);

    //     $response = $controller->showDeck($this->sessionMock);

    //     $this->assertNotNull($response);
    //     $this->assertInstanceOf(Response::class, $response);
    // }

    // public function testDrawCard(): void
    // {
    //     $deck = [$this->createMock(Card::class)];

    //     $this->sessionMock->expects($this->once())
    //         ->method('has')
    //         ->with('deck')
    //         ->willReturn(true);
    //     $this->sessionMock->expects($this->once())
    //         ->method('get')
    //         ->with('deck')
    //         ->willReturn($deck);
    //     $this->sessionMock->expects($this->once())
    //         ->method('set')
    //         ->with('deck', []);

    //     $controller = $this->getMockBuilder(CardGameController::class)
    //         ->onlyMethods(['render'])
    //         ->getMock();

    //     $controller->expects($this->once())
    //         ->method('render')
    //         ->with('card/draw.html.twig', $this->isType('array'))
    //         ->willReturn(new Response());

    //     $container = $this->createMock(ContainerInterface::class);
    //     $controller->setContainer($container);

    //     $response = $controller->drawCard($this->sessionMock);

    //     $this->assertNotNull($response);
    //     $this->assertInstanceOf(Response::class, $response);
    // }

    // Similar methods can be written for other actions (shuffle, draw multiple, deal cards, etc.)
}
