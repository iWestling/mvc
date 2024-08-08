<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;

class TexasHoldemController extends AbstractController
{
    #[Route('/proj', name: 'proj_home')]
    public function index(): Response
    {
        return $this->render('texas/index.html.twig');
    }

    #[Route('/proj/about', name: 'proj_about')]
    public function about(): Response
    {
        return $this->render('texas/about.html.twig');
    }

    #[Route('/proj/start', name: 'proj_start', methods: ['GET', 'POST'])]
    public function startGame(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $chips = $request->request->get('chips', 1000);
            $level1 = $request->request->get('level1', 'normal');
            $level2 = $request->request->get('level2', 'normal');

            $game = new TexasHoldemGame();
            $game->addPlayer(new Player('You', $chips, 'intelligent'));
            $game->addPlayer(new Player('Computer 1', $chips, $level1));
            $game->addPlayer(new Player('Computer 2', $chips, $level2));

            $game->dealInitialCards();

            $session->set('game', $game);

            return $this->redirectToRoute('proj_play');
        }

        return $this->render('texas/start.html.twig');
    }

    #[Route('/proj/play', name: 'proj_play', methods: ['GET', 'POST'])]
    public function playRound(Request $request, SessionInterface $session): Response
    {
        $game = $session->get('game');

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $raiseAmount = $request->request->get('raiseAmount', 0);
            $game->playRound($action, $raiseAmount);
            $session->set('game', $game);
        }

        if ($game->isGameOver()) {
            return $this->redirectToRoute('proj_winner');
        }

        return $this->render('texas/game.html.twig', [
            'game' => $game
        ]);
    }

    #[Route('/proj/winner', name: 'proj_winner')]
    public function showWinner(SessionInterface $session): Response
    {
        $game = $session->get('game');

        return $this->render('texas/winner.html.twig', [
            'winners' => $game->getWinners(),
        ]);
    }
}
