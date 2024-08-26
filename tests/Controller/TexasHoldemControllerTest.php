<?php

namespace App\Tests\Controller;

use App\Controller\TexasHoldemController;
use App\Service\GameHandlerService;
use App\Service\GameInitializer;
use App\Service\ScoreService;
use App\CardGame\TexasHoldemGame;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Container\ContainerInterface;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class TexasHoldemControllerTest extends TestCase
{
    /** @var GameHandlerService&\PHPUnit\Framework\MockObject\MockObject */
    private $gameHandlerService;

    /** @var GameInitializer&\PHPUnit\Framework\MockObject\MockObject */
    private $gameInitializer;

    /** @var ScoreService&\PHPUnit\Framework\MockObject\MockObject */
    private $scoreService;

    /** @var TexasHoldemController&\PHPUnit\Framework\MockObject\MockObject */
    private $controller;

    /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var TexasHoldemGame&\PHPUnit\Framework\MockObject\MockObject */
    private $game;

    protected function setUp(): void
    {
        $this->gameHandlerService = $this->createMock(GameHandlerService::class);
        $this->gameInitializer = $this->createMock(GameInitializer::class);
        $this->scoreService = $this->createMock(ScoreService::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->game = $this->createMock(TexasHoldemGame::class);

        // Create a partial mock for the TexasHoldemController
        $this->controller = $this->getMockBuilder(TexasHoldemController::class)
            ->setConstructorArgs([
                $this->gameHandlerService,
                $this->gameInitializer,
                $this->scoreService,
            ])
            ->onlyMethods(['render', 'redirectToRoute']) // Mock render and redirectToRoute methods
            ->getMock();

        // Manually set the container
        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);

        // Mock the render method to return a simple Response
        $this->controller->method('render')->willReturn(new Response());

        // Ensure that redirectToRoute() returns a RedirectResponse
        $this->controller->method('redirectToRoute')->willReturn(new RedirectResponse('/proj_play'));
    }

    public function testIndex(): void
    {
        $response = $this->controller->index();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAbout(): void
    {
        $response = $this->controller->about();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDatabase(): void
    {
        $response = $this->controller->database();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testStartGamePostRequest(): void
    {
        // Create a mock POST request
        $request = new Request([], [
            'chips' => 1000,
            'level1' => 'normal',
            'level2' => 'normal'
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $game = $this->createMock(TexasHoldemGame::class);

        // Expect gameInitializer to be called
        $this->gameInitializer->expects($this->once())
            ->method('initializeGame')
            ->with(1000, 'normal', 'normal')
            ->willReturn($game);

        $this->gameInitializer->expects($this->once())
            ->method('saveGameToSession')
            ->with($this->session, $game);

        // Mock redirectToRoute to return a RedirectResponse
        $this->controller->/** @scrutinizer ignore-call */ method('redirectToRoute')->willReturn(new RedirectResponse('/proj_play'));

        // Call the startGame method
        $response = $this->controller->startGame($request, $this->session);

        // Assert that the response is a RedirectResponse
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        /** @var RedirectResponse $response */
        $this->assertEquals('/proj_play', $response->getTargetUrl());
    }

    public function testStartGameGetRequest(): void
    {
        $request = new Request();
        $response = $this->controller->startGame($request, $this->session);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPlayRoundWhenNoGameInSession(): void
    {
        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn(null);

        $response = $this->controller->playRound(new Request(), $this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testPlayRoundWithAllInScenario(): void
    {
        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn($this->game);

        $this->gameHandlerService->expects($this->once())
            ->method('handleAllInScenario')
            ->willReturn(true);

        $this->gameHandlerService->expects($this->once())
            ->method('renderGameView')
            ->with($this->game)
            ->willReturn(new Response());

        $response = $this->controller->playRound(new Request(), $this->session);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testPlayRoundWithNormalScenario(): void
    {
        $this->session->/** @scrutinizer ignore-call */ expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['game'], ['current_action_index', 0])
            ->willReturnOnConsecutiveCalls($this->game, 0);

        $this->gameHandlerService->/** @scrutinizer ignore-call */ expects($this->once())
            ->method('handleAllInScenario')
            ->willReturn(false);

        $this->gameHandlerService->/** @scrutinizer ignore-call */ expects($this->once())
            ->method('advancePhaseIfNeeded')
            ->willReturn(null);

        $this->gameHandlerService->/** @scrutinizer ignore-call */ expects($this->once())
            ->method('renderGameView')
            ->with($this->game)
            ->willReturn(new Response());

        $response = $this->controller->playRound(new Request(), $this->session);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSubmitScoreWithValidData(): void
    {
        $request = new Request([], [
            'username' => 'testuser',
            'age' => 30,
            'score' => 1000
        ]);

        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn($this->game);

        $this->scoreService->expects($this->once())
            ->method('submitScore')
            ->with('testuser', 30, 1000)
            ->willReturn(new JsonResponse(['success' => 'Score submitted successfully!'], Response::HTTP_OK));

        $response = $this->controller->submitScore($request, $this->session);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSubmitScoreWithInvalidUsername(): void
    {
        $request = new Request([], [
            'username' => null,
            'age' => 30,
            'score' => 1000
        ]);

        $response = $this->controller->submitScore($request, $this->session);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testSubmitScoreWhenNoGameInSession(): void
    {
        $request = new Request([], [
            'username' => 'testuser',
            'age' => 30,
            'score' => 1000
        ]);

        // Simulate that no game is present in the session
        $this->session->method('get')->with('game')->willReturn(null);

        // Ensure that the ScoreService returns a successful response
        $this->scoreService->/** @scrutinizer ignore-call */ expects($this->once())
            ->method('submitScore')
            ->with('testuser', 30, 1000)
            ->willReturn(new JsonResponse(['success' => 'Score submitted successfully!'], Response::HTTP_OK));

        // Call the submitScore method
        $response = $this->controller->submitScore($request, $this->session);

        // Assert that the response is a RedirectResponse because no game is present in the session
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }


    public function testStartNewRoundWhenNoGameInSession(): void
    {
        $this->session->method('get')->with('game')->willReturn(null);

        $response = $this->controller->startNewRound($this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testStartNewRoundWithGameInSession(): void
    {
        $this->session->method('get')->with('game')->willReturn($this->game);

        $this->game->expects($this->once())->method('startNewRound');

        $response = $this->controller->startNewRound($this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testApiPage(): void
    {
        $response = $this->controller->apiPage();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
