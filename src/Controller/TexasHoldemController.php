<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\PlayerActionHandler;

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
            $chips = (int)$request->request->get('chips', 1000);
            $level1 = (string)$request->request->get('level1', 'normal');
            $level2 = (string)$request->request->get('level2', 'normal');

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
        // Retrieve the game from the session
        $game = $session->get('game');

        // Check if $game is an instance of TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        if ($request->isMethod('POST')) {
            $action = (string) $request->request->get('action', 'check'); // Cast to string and provide a default action
            $raiseAmount = (int) $request->request->get('raiseAmount', 0);

            // Create an instance of PotManager
            $potManager = $game->getPotManager();

            // Create an instance of PlayerActionHandler
            $playerActionHandler = new PlayerActionHandler($potManager);

            // Call the playRound method with the appropriate parameters
            $game->playRound($playerActionHandler, $action, $raiseAmount);

            // Save the updated game state to the session
            $session->set('game', $game);
        }

        // Get the minimum chips from the game
        $minChips = $game->getMinimumChips();

        // Render the game view with the relevant data
        return $this->render('texas/game.html.twig', [
            'game' => $game,
            'isGameOver' => $game->isGameOver(),
            'winners' => $game->getWinners(),
            'minChips' => $minChips,
        ]);
    }



    #[Route('/proj/new-round', name: 'proj_new_round', methods: ['POST'])]
    public function startNewRound(SessionInterface $session): Response
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        // Create an instance of PotManager
        $potManager = $game->getPotManager(); // Assuming there's a method to get PotManager from the game

        // Create an instance of PlayerActionHandler
        $playerActionHandler = new PlayerActionHandler($potManager);

        // Pass the PlayerActionHandler instance to startNewRound
        $game->startNewRound($playerActionHandler);

        $session->set('game', $game);

        return $this->redirectToRoute('proj_play');
    }
}
