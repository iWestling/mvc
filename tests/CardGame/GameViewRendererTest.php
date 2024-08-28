<?php

namespace App\Tests\CardGame;

use App\CardGame\GameViewRenderer;
use App\CardGame\TexasHoldemGame;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;

class GameViewRendererTest extends TestCase
{
    public function testRenderGameView(): void
    {
        // Mock the Twig environment
        $twig = $this->createMock(Environment::class);

        $game = $this->createMock(TexasHoldemGame::class);

        $twig->expects($this->once())
            ->method('render')
            ->with('texas/game.html.twig', $this->isType('array'))
            ->willReturn('rendered_template');

        $renderer = new GameViewRenderer($twig);

        $response = $renderer->renderGameView($game);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('rendered_template', $response->getContent());
    }
}
