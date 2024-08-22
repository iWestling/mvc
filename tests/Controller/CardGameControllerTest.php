<?php

namespace App\Tests\Controller;

use App\Controller\CardGameController;
use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
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

    public function testDeleteSession(): void
    {
        // Mock the session clear method
        $this->sessionMock->expects($this->once())
            ->method('clear');

        // Create a partial mock of the controller to mock addFlash and redirectToRoute methods
        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        // Expect the addFlash method to be called once with the correct parameters
        $controller->expects($this->once())
            ->method('addFlash')
            ->with('notice', 'Session data has been deleted.');

        // Expect the redirectToRoute method to be called once and return a RedirectResponse
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('session_show')
            ->willReturn(new RedirectResponse('/session/show'));

        // Mock the container, which is required for the controller
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Call the deleteSession method with the mocked session
        $response = $controller->deleteSession($this->sessionMock);

        // Assert that the response is not null and is an instance of RedirectResponse
        $this->assertNotNull($response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    public function testHome(): void
    {
        // Create a partial mock of the controller to mock the render method
        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Expect the render method to be called once with the correct template
        $controller->expects($this->once())
            ->method('render')
            ->with('card/home.html.twig')
            ->willReturn(new Response());

        // Mock the container, which is required for the controller
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Call the home method
        $response = $controller->home();

        // Assert that the response is not null and is an instance of Response
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowDeck(): void
    {
        // Create the DeckOfCards and generate the expected deck of cards
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        // Map deck to CardGraphic paths
        $cardPaths = array_map(function (Card $card) {
            $cardGraphic = new CardGraphic($card->getValue());
            return $cardGraphic->getAsString();
        }, $deck);

        // Expect the session to set 'deck' with the generated deck
        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', $deck);

        // Create a partial mock of the controller to mock the render method
        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Expect the render method to be called with the correct template and data
        $controller->expects($this->once())
            ->method('render')
            ->with('card/deck.html.twig', [
                'deck' => $deck,
                'cardPaths' => $cardPaths,
                'remainingCards' => count($deck),
            ])
            ->willReturn(new Response());

        // Mock the container, which is required for the controller
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Call the showDeck method
        $response = $controller->showDeck($this->sessionMock);

        // Assert that the response is not null and is an instance of Response
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    // public function testShuffleDeck(): void
    // {
    //     // Create the DeckOfCards and generate the deck of cards
    //     $deckOfCards = new DeckOfCards();
    //     $deck = $deckOfCards->getCards();

    //     // Shuffle the deck manually for test purposes
    //     shuffle($deck);

    //     // We no longer check the exact order but verify that it's an array of Card objects
    //     $this->sessionMock->expects($this->once())
    //         ->method('set')
    //         ->with('deck', $this->callback(function ($value) {
    //             // Ensure the value is an array of Card objects
    //             return is_array($value) && count($value) === 52 && $value[0] instanceof Card;
    //         }));

    //     // Map deck to CardGraphic paths
    //     $cardPaths = array_map(function (Card $card) {
    //         $cardGraphic = new CardGraphic($card->getValue());
    //         return $cardGraphic->getAsString();
    //     }, $deck);

    //     // Create a partial mock of the controller to mock the render method
    //     $controller = $this->getMockBuilder(CardGameController::class)
    //         ->onlyMethods(['render'])
    //         ->getMock();

    //     // Expect the render method to be called with the correct template and data
    //     $controller->expects($this->once())
    //         ->method('render')
    //         ->with('card/shuffle.html.twig', [
    //             'deck' => $deck,
    //             'cardPaths' => $cardPaths,
    //             'remainingCards' => count($deck),
    //         ])
    //         ->willReturn(new Response());

    //     // Mock the container, which is required for the controller
    //     $container = $this->createMock(ContainerInterface::class);
    //     $controller->setContainer($container);

    //     // Call the shuffleDeck method
    //     $response = $controller->shuffleDeck($this->sessionMock);

    //     // Assert that the response is not null and is an instance of Response
    //     $this->assertNotNull($response);
    //     $this->assertInstanceOf(Response::class, $response);
    // }
    public function testDrawCardWithNoDeckInSession(): void
    {
        // Session does not have a deck
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(false);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No cards in deck. Resetting deck, please try again.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawCard($this->sessionMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testDrawCardWithEmptyDeck(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck')
            ->willReturn([]);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No more cards left in the deck. Resetting deck, please try again.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawCard($this->sessionMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testDrawCardSuccessfully(): void
    {
        $deck = [new Card(1), new Card(2)];

        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck')
            ->willReturn($deck);

        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', $this->callback(function ($value) {
                return is_array($value) && count($value) === 1; // 1 card removed
            }));

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('card/draw.html.twig', [
                'drawnCardPaths' => ['img/carddeck/hearts_ace.png'],
                'remainingCards' => 1,
            ])
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawCard($this->sessionMock);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testDrawCardsPost(): void
    {
        // Create a real Request object with the required POST data
        $request = new Request([], ['number' => 3]); // Simulate a POST request with 'number' => 3

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_draw_number', ['number' => 3])
            ->willReturn(new RedirectResponse('/card/deck/draw/3'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawCardsPost($request);

        // Cast the response to RedirectResponse to access getTargetUrl()
        /** @var RedirectResponse $response */
        // Assert that the response is not null and is a RedirectResponse
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/card/deck/draw/3', $response->getTargetUrl());
    }



    public function testDrawNumberCardsWithNoDeckInSession(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(false);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No cards in deck. Resetting deck.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawNumberCards($this->createMock(Request::class), $this->sessionMock, 3);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testDrawNumberCardsWithEmptyDeck(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck')
            ->willReturn([]);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No more cards left in the deck. Resetting deck, please try again.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->drawNumberCards($this->createMock(Request::class), $this->sessionMock, 3);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    public function testDealCardsPost(): void
    {
        // Create a real Request object with the required POST data
        $request = new Request([], ['players' => 4, 'cards' => 5]); // Simulate a POST request with 'players' => 4 and 'cards' => 5

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deal', ['players' => 4, 'cards' => 5])
            ->willReturn(new RedirectResponse('/card/deck/deal/4/5'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->dealCardsPost($request);

        // Cast the response to RedirectResponse to access getTargetUrl()
        /** @var RedirectResponse $response */
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/card/deck/deal/4/5', $response->getTargetUrl());
    }

    public function testDealCardsGetWithNoDeckInSession(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(false);

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No cards in deck. Resetting deck, please try again.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->dealCardsGet($this->sessionMock, 2, 5);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    public function testDealCardsGetWithEmptyDeck(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('has')
            ->with('deck')
            ->willReturn(true);

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn([]); // Empty deck

        $controller = $this->getMockBuilder(CardGameController::class)
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'No more cards left in the deck. Resetting deck, please try again.');

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('card_deck')
            ->willReturn(new RedirectResponse('/card/deck'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->dealCardsGet($this->sessionMock, 2, 5);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
