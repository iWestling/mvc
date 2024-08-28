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

        $this->controller = $this->getMockBuilder(TexasHoldemController::class)
            ->setConstructorArgs([
                $this->gameHandlerService,
                $this->gameInitializer,
                $this->scoreService,
            ])
            ->onlyMethods(['render', 'redirectToRoute'])
            ->getMock();

        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);
        $this->controller->method('render')->willReturn(new Response());
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
        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn(null);

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
        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn(null);

        $response = $this->controller->startNewRound($this->session);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testStartNewRoundWithGameInSession(): void
    {
        $this->session->/** @scrutinizer ignore-call */ method('get')->with('game')->willReturn($this->game);

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

    public function testPlayRoundWithNormalScenario(): void
    {
        $this->session->/** @scrutinizer ignore-call */ method('get')
            ->willReturnMap([
                ['game', null, $this->game],
                ['current_action_index', 0, 0]
            ]);

        $this->gameHandlerService->expects($this->once())
            ->method('handleAllInScenario')
            ->willReturn(false);

        $this->gameHandlerService->expects($this->once())
            ->method('advancePhaseIfNeeded')
            ->willReturn(null);

        $this->gameHandlerService->expects($this->once())
            ->method('handleGameStatus')
            ->with($this->game, null)
            ->willReturn(new Response());

        $response = $this->controller->playRound(new Request(), $this->session);

        $this->assertInstanceOf(Response::class, $response);
    }


    public function testPlayRoundWithPhaseAdvanced(): void
    {
        $request = new Request();

        $this->session->/** @scrutinizer ignore-call */ method('get')
            ->willReturnMap([
                ['game', null, $this->game],
                ['current_action_index', 0, 0]
            ]);

        $this->gameHandlerService->expects($this->once())
            ->method('advancePhaseIfNeeded')
            ->willReturn('phase_advanced');

        $this->gameHandlerService->expects($this->once())
            ->method('handleGameStatus')
            ->with($this->game, 'phase_advanced')
            ->willReturn(new Response('', Response::HTTP_FOUND, ['Location' => '/proj/play']));

        $response = $this->controller->playRound($request, $this->session);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/proj/play', $response->headers->get('Location'));
    }

    public function testPlayRoundWithGameOver(): void
    {
        $request = new Request();

        $this->session->/** @scrutinizer ignore-call */ method('get')
            ->willReturnMap([
                ['game', null, $this->game],
                ['current_action_index', 0, 0]
            ]);

        $this->gameHandlerService->expects($this->once())
            ->method('advancePhaseIfNeeded')
            ->willReturn('game_over');

        $this->gameHandlerService->expects($this->once())
            ->method('handleGameStatus')
            ->with($this->game, 'game_over')
            ->willReturn(new Response());

        $response = $this->controller->playRound($request, $this->session);

        $this->assertInstanceOf(Response::class, $response);
    }



}
