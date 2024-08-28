<?php

namespace App\Tests\Controller;

use App\Controller\GamePlayerController;
use App\Repository\GamePlayerRepository;
use App\Repository\ScoresRepository;
use App\Service\PlayerScoreResetService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use App\Entity\GamePlayer;
use DateTime;

class GamePlayerControllerTest extends WebTestCase
{
    /** @var MockObject&PlayerScoreResetService */
    private MockObject $resetServiceMock;

    protected function setUp(): void
    {
        $this->resetServiceMock = $this->createMock(PlayerScoreResetService::class);
    }

    public function testIndex(): void
    {
        $controller = $this->getMockBuilder(GamePlayerController::class)
            ->setConstructorArgs([$this->resetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/database.html.twig', ['controller_name' => 'GamePlayerController'])
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->index();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowAllGamePlayers(): void
    {
        $gamePlayerRepoMock = $this->createMock(GamePlayerRepository::class);

        $player1 = $this->createMock(GamePlayer::class);
        $player1->method('getId')->willReturn(1);
        $player1->method('getUsername')->willReturn('Player1');
        $player1->method('getAge')->willReturn(30);

        $player2 = $this->createMock(GamePlayer::class);
        $player2->method('getId')->willReturn(2);
        $player2->method('getUsername')->willReturn('Player2');
        $player2->method('getAge')->willReturn(25);

        $gamePlayerRepoMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$player1, $player2]);

        $controller = $this->getMockBuilder(GamePlayerController::class)
            ->setConstructorArgs([$this->resetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/gameplayer.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->showAllGamePlayers($gamePlayerRepoMock);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }


    public function testShowAllScoresWithPlayers(): void
    {
        $scoresRepoMock = $this->createMock(ScoresRepository::class);

        $scoresRepoMock->expects($this->once())
            ->method('findAllWithPlayersOrderedByScore')
            ->willReturn([
                ['score' => 100, 'username' => 'Player1', 'age' => 30, 'date' => new DateTime()],
                ['score' => 50, 'username' => 'Player2', 'age' => 25, 'date' => new DateTime()],
            ]);

        $controller = $this->getMockBuilder(GamePlayerController::class)
            ->setConstructorArgs([$this->resetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('texas/highscores.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->showAllScoresWithPlayers($scoresRepoMock);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testResetPlayerDatabase(): void
    {
        $this->resetServiceMock->expects($this->once())
            ->method('resetPlayerData')
            ->willReturn(new Response('Player and score database reset successful'));

        $controller = new GamePlayerController($this->resetServiceMock);

        $response = $controller->resetPlayerDatabase();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Player and score database reset successful', $response->getContent());
    }
}
