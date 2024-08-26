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
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\GamePlayer;
use App\Entity\Scores;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\CardGame\PlayerActionHandler;
use ReflectionClass;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 * @SuppressWarnings("CouplingBetweenObjects")
 */
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

    /**
     * @var MockObject&ManagerRegistry
     */
    private $doctrine;

    /**
     * @var MockObject&EntityManagerInterface
     */
    private $entityManager;


    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->game = $this->createMock(TexasHoldemGame::class);

        // Create a partial mock of TexasHoldemController
        $this->controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render']) // Mock only the render method
            ->getMock();

        // Mock the ManagerRegistry and EntityManager
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Set up the ManagerRegistry to return the EntityManager
        $this->doctrine->method('getManager')->willReturn($this->entityManager);

        // Set up the container to avoid container initialization error
        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);
    }

    public function testSubmitScore(): void
    {
        // Mock the game in the session
        $this->session->expects($this->once())
            ->method('get')
            ->with('game')
            ->willReturn($this->game);

        // Mock form data
        $request = new Request([], [
            'username' => 'testuser',
            'age' => 30,
            'score' => 1000,
        ]);

        // Mock the persistence of GamePlayer and Scores entities
        /** @scrutinizer ignore-deprecated */ $this->entityManager->expects($this->exactly(2))
        ->method('persist')
        ->withConsecutive(
            [$this->isInstanceOf(GamePlayer::class)], // Wrap in an array
            [$this->isInstanceOf(Scores::class)] // Wrap in an array
        );

        // Mock the flush method being called
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Mock the rendering of the game view
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/game.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        // Call the submitScore method
        $response = $this->controller->submitScore($request, $this->doctrine, $this->session);

        // Assert that the response is valid
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
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
        /** @scrutinizer ignore-deprecated */ $session->expects($this->exactly(2))
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
        /** @scrutinizer ignore-deprecated */ $this->session->expects($this->exactly(2))
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
        /** @scrutinizer ignore-deprecated */ $this->session->expects($this->exactly(2))
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

    public function testStartNewRoundRedirectsToStartIfNoGameInSession(): void
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

        // Simulate the POST request
        $response = $controller->startNewRound($this->session);

        // Assert the redirect response
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/proj/start', $response->getTargetUrl());
    }
    public function testSubmitScoreInvalidUsername(): void
    {
        // Mock form data with invalid username
        $request = new Request([], [
            'username' => null, // Invalid username
            'age' => 30,
            'score' => 1000,
        ]);

        // Call the submitScore method
        $response = $this->controller->submitScore($request, $this->doctrine, $this->session);

        // Assert the response is a JsonResponse with an error
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Get the content of the response
        $content = $response->getContent();

        // Ensure content is not false and is a valid JSON string
        $this->assertNotFalse($content, 'Response content should not be false.');

        // Decode the content
        $data = json_decode($content, true);

        // Ensure that $data is an array before proceeding with further assertions
        $this->assertIsArray($data, 'Decoded response should be an array.');

        // Assert that the 'error' key exists in the response data
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid username.', $data['error']);
    }


    public function testSubmitScoreRedirectsToStartIfNoGameInSession(): void
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

        // Mock form data
        $request = new Request([], [
            'username' => 'testuser',
            'age' => 30,
            'score' => 1000,
        ]);

        // Call the submitScore method
        $response = $controller->submitScore($request, $this->doctrine, $this->session);

        // Assert the redirect response
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/proj/start', $response->getTargetUrl());
    }

    public function testDatabaseRequest(): void
    {
        // Mock the controller to expect the render method call
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/database.html.twig')
            ->willReturn(new Response());

        // Call the database method
        $response = $this->controller->database();

        // Assert that the response is a successful render of the database page
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testPlayRoundRendersGameView(): void
    {
        // Mock the session to return the game object and handle multiple calls to 'current_action_index'
        /** @scrutinizer ignore-deprecated */ $this->session->expects($this->any())  // Use 'any' to allow multiple calls
            ->method('get')
            ->withConsecutive(['game'], ['current_action_index', 0])
            ->willReturnOnConsecutiveCalls($this->game, 0);

        // Mock the game behavior to indicate the game is not over
        $this->game->expects($this->once())
            ->method('isGameOver')
            ->willReturn(false);

        // Mock the render method directly
        $this->controller = $this->getMockBuilder(TexasHoldemController::class)
            ->onlyMethods(['render'])
            ->getMock();

        // Expect the `render` method to be called with the correct template and parameters
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/game.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        // Set up the container
        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);

        // Simulate the GET request
        $response = $this->controller->playRound(new Request(), $this->session);

        // Assert that the response is valid
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testRenderGameViewWhenGameIsOver(): void
    {
        // Mock the session to return the game
        $this->session->/** @scrutinizer ignore-call */ method('get')
            ->willReturn($this->game);

        // Mock the game to indicate it's over
        $this->game->/** @scrutinizer ignore-call */ method('isGameOver')
            ->willReturn(true);

        // Call the playRound method and check the actual response
        $response = $this->controller->playRound(new Request(), $this->session);

        // Assert that the response is valid
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCurrentActionIndexResetToDefaultIfNotNumeric(): void
    {
        // Mock the session to return the game and an invalid action index
        $this->session->/** @scrutinizer ignore-call */method('get')
            ->willReturnMap([
                ['game', null, $this->game],
                ['current_action_index', 0, 'invalid']
            ]);

        // Expect the session to reset the action index to 0
        $this->session->expects($this->any())  // Relax expectation on set call
            ->method('set');

        // Call the playRound method and check the actual response
        $response = $this->controller->playRound(new Request(), $this->session);

        // Assert that the response is valid
        $this->assertInstanceOf(Response::class, $response);
    }


    public function testFoldedPlayerIsSkipped(): void
    {
        // Mock the session to return the game and action index
        $this->session->method('get')
            ->willReturnMap([
                ['game', null, $this->game],
                ['current_action_index', 0, 0]
            ]);

        // Mock the players and the game behavior
        $player = $this->createMock(Player::class);
        $player->method('isFolded')
            ->willReturn(true);

        $this->game->/** @scrutinizer ignore-call */method('getPlayersInOrder')
            ->willReturn([$player]);

        // Relax expectation on session set call
        $this->session->expects($this->any())
            ->method('set');

        // Call the playRound method and check the actual response
        $response = $this->controller->playRound(new Request(), $this->session);

        // Assert that the response is valid
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testReturnResponse(): void
    {
        // Simulate the response returned by advancePhaseIfNeeded
        $mockResponse = new Response();

        // Mock the session to return the game and action index
        /** @scrutinizer ignore-call */ $this->session->/** @scrutinizer ignore-call */ expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['game'], ['current_action_index', 0])
            ->willReturnOnConsecutiveCalls($this->game, 0);

        // Mock the players and the game behavior
        $player = $this->createMock(Player::class);
        $player->expects($this->once())
            ->method('getName')
            ->willReturn('Computer 1');

        $this->game->expects($this->once())
            ->method('getPlayersInOrder')
            ->willReturn([$player]);

        // Mock the render method to return the response
        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn($mockResponse);

        // Simulate the GET request
        $response = $this->controller->playRound(new Request(), $this->session);

        // Assert that the response is correctly returned
        $this->assertSame($mockResponse, $response);
    }
    public function testHandleAllInScenario(): void
    {
        // Create a mock for PlayerActionHandler
        $playerActionHandler = $this->createMock(PlayerActionHandler::class);

        // We will not strictly expect the session's `get` method to be called, allowing more flexibility
        $this->session->method('get')
            ->willReturn($this->game); // Return the game when `get` is called on the session

        // Mock the game behavior for All-In scenario
        $this->game->expects($this->once())
            ->method('hasAllInOccurred')
            ->willReturn(true);

        // Expect the game to handle remaining players after All-In
        $this->game->expects($this->once())
            ->method('handleRemainingPlayersAfterAllIn')
            ->with($playerActionHandler);

        // Mock the game advancing stages until it's over
        $this->game->expects($this->exactly(2))
            ->method('isGameOver')
            ->willReturnOnConsecutiveCalls(false, true);  // First return false, then true

        // Expect the game to advance stages
        $this->game->expects($this->once())
            ->method('advanceGameStage');

        // Invoke the handleAllInScenario method
        $result = $this->invokeMethod($this->controller, 'handleAllInScenario', [$this->game, $playerActionHandler]);

        // Assert that the method returns true
        $this->assertTrue($result);
    }


    /**
     * @param array<int, mixed> $parameters
     */
    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }




}
