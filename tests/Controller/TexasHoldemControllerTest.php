<?php

namespace App\Tests\Controller;

use App\Controller\TexasHoldemController;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\PlayerActionHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PHPUnit\Framework\MockObject\MockObject;

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

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->game = $this->createMock(TexasHoldemGame::class);
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
