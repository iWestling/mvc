<?php

namespace App\CardGame;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class GameViewRenderer
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderGameView(TexasHoldemGame $game): Response
    {
        return new Response($this->twig->render('texas/game.html.twig', [
            'game' => $game,
            'isGameOver' => $game->isGameOver(),
            'winners' => $game->getWinners(),
            'minChips' => $game->getMinimumChips(),
            'pot' => $game->getPotManager()->getPot(),
            'currentStage' => $game->getStageManager()->getCurrentStage(),
            'success_message' => 'Your score has been successfully submitted!'
        ]));
    }
}
