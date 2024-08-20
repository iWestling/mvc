<?php

namespace App\Tests\Controller;

use App\Controller\TexasHoldemController;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TexasHoldemControllerTest extends WebTestCase
{
    /**
     * @var MockObject&SessionInterface
     */
    private $session;

    /**
     * @var MockObject&TexasHoldemGame
     */
    private $game;

    /**
     * @var MockObject&TexasHoldemController
     */
    private $controller;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->game = $this->createMock(TexasHoldemGame::class);

        // Create a partial mock of TexasHoldemController
        $this->controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render']) // Mock only the render method
            ->getMock();

        // Set up the container to avoid container initialization error
        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);
    }

    public function testStartGameGetRequest(): void
    {
        // Simulate a GET request
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/proj/start'], null);

        // Set up expectations on the mock controller
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/start.html.twig')
            ->willReturn(new Response());

        // Call the startGame method
        $response = $this->controller->startGame($request, $this->session);

        // Assert that the response is a successful render of the start page
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAboutRequest(): void
    {
        // Set up expectations on the mock controller
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/about.html.twig')
            ->willReturn(new Response());

        // Call the about method
        $response = $this->controller->about();

        // Assert that the response is a successful render of the about page
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testStartTexasHoldemGame(): void
    {
        // Mock the session interface
        $session = $this->createMock(SessionInterface::class);

        // Set expectations for the session interface
        $session->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['game', $this->isInstanceOf(TexasHoldemGame::class)],
                ['current_action_index', 0]
            );

        // Mock the controller
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        // Mock the redirection response
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('proj_play')
            ->willReturn(new RedirectResponse('/proj/play'));

        // Set up the container mock
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate the POST request to start the game
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $request->request->replace(['chips' => 1500, 'level1' => 'normal', 'level2' => 'intelligent']);

        // Call the startGame method on the controller
        $response = $controller->startGame($request, $session);

        // Assert that the response is a redirect to the play route
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/proj/play', $response->headers->get('Location'));
    }



    public function testIndexPage(): void
    {
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/index.html.twig')
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->index();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testPlayRoundGet(): void
    {
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Mock the game and session
        $this->session->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['game'], ['current_action_index', 0])
            ->willReturnOnConsecutiveCalls($this->game, 0);

        // Mock the game behavior
        $this->game->expects($this->once())
            ->method('isGameOver')
            ->willReturn(false);

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/game.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        // Set up the container
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->playRound(new Request(), $this->session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testPlayRoundRedirectsToStartIfNoGameInSession(): void
    {
        // Mock the session to return null when getting the 'game'
        $this->session->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn(null);

        // Mock the controller and expect a redirect to 'proj_start'
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('proj_start')
            ->willReturn(new RedirectResponse('/proj/start'));

        // Set up the container
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate the GET request
        $response = $controller->playRound(new Request(), $this->session);

        // Assert the redirect response
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/proj/start', $response->getTargetUrl());
    }

    public function testPlayRoundHandlesComputerPlayerAction(): void
    {
        // Mock the session to return the game object and action index
        $this->session->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['game'], ['current_action_index', 0])
            ->willReturnOnConsecutiveCalls($this->game, 0);

        // Mock the game to simulate a computer player's turn
        $player = $this->createMock(Player::class);
        $player->expects($this->once())
            ->method('getName')
            ->willReturn('Computer 1');

        $this->game->expects($this->once())
            ->method('getPlayersInOrder')
            ->willReturn([$player]);

        // Mock the computer player's decision and action handling
        $player->expects($this->once())
            ->method('makeDecision')
            ->willReturn('fold');
        $this->game->expects($this->once())
            ->method('handleAction')
            ->with($player, 'fold');

        // Expect the session to update the current action index
        $this->session->expects($this->once())
            ->method('set')
            ->with('current_action_index', 1);

        // Mock the controller and handle the game view rendering indirectly
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Adjust the expectation to check if render is called
        $controller->expects($this->once())
            ->method('render')
            ->with('texas/game.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        // Set up the container
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate the GET request
        $response = $controller->playRound(new Request(), $this->session);

        // Assert the response is valid
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStartNewRound(): void
    {
        $controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        // Mock the session to return the game object
        $this->session->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($this->game);

        // Mock the game behavior
        $this->game->expects($this->once())
            ->method('startNewRound');

        // Expect the redirection
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('proj_play')
            ->willReturn(new RedirectResponse('/proj/play'));

        // Set up the container
        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->startNewRound($this->session);

        $this->assertNotNull($response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
