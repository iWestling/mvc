<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\CardGame\TexasHoldemGame;
use App\Service\ScoreService;
use App\Service\GameInitializer;
use App\Service\GameHandlerService;
use App\CardGame\PlayerActionHandler;

class TexasHoldemController extends AbstractController
{
    private GameInitializer $gameInitializer;
    private GameHandlerService $gameHandlerService;
    private ScoreService $scoreService;

    public function __construct(GameHandlerService $gameHandlerService, GameInitializer $gameInitializer, ScoreService $scoreService)
    {
        $this->gameHandlerService = $gameHandlerService;
        $this->gameInitializer = $gameInitializer;
        $this->scoreService = $scoreService;
    }

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

    #[Route('/proj/about/database', name: 'proj_database')]
    public function database(): Response
    {
        return $this->render('texas/database.html.twig');
    }

    #[Route('/proj/start', name: 'proj_start', methods: ['GET', 'POST'])]
    public function startGame(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $chips = (int)$request->request->get('chips', 1000);
            $level1 = (string)$request->request->get('level1', 'normal');
            $level2 = (string)$request->request->get('level2', 'normal');

            $game = $this->gameInitializer->initializeGame($chips, $level1, $level2);
            $this->gameInitializer->saveGameToSession($session, $game);

            return $this->redirectToRoute('proj_play');
        }

        return $this->render('texas/start.html.twig');
    }


    #[Route('/proj/play', name: 'proj_play', methods: ['GET', 'POST'])]
    public function playRound(Request $request, SessionInterface $session): Response
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        $playerActionHandler = new PlayerActionHandler($game->getPotManager());

        if ($this->gameHandlerService->handleAllInScenario($game, $playerActionHandler)) {
            return $this->gameHandlerService->renderGameView($game);
        }

        $playersInOrder = $game->getPlayersInOrder();
        $currentActionIndex = $session->get('current_action_index', 0);
        $currentActionIndex = is_numeric($currentActionIndex) ? (int) $currentActionIndex : 0;
        $totalPlayers = count($playersInOrder);

        while ($currentActionIndex < $totalPlayers) {
            $player = $playersInOrder[$currentActionIndex];

            if ($player->isFolded()) {
                $currentActionIndex++;
                continue;
            }

            if ($player->getName() === 'You') {
                if ($request->isMethod('POST')) {
                    $action = (string) $request->request->get('action', 'check');
                    $raiseAmount = (int) $request->request->get('raiseAmount', 0);

                    $game->processPlayerAction($playerActionHandler, $action, $raiseAmount);
                    $currentActionIndex++;
                    $session->set('current_action_index', $currentActionIndex);

                    return $this->redirectToRoute('proj_play');
                }
                break;
            }

            $decision = $player->makeDecision($game->getCommunityCardManager()->getCommunityCards(), $game->getPotManager()->getCurrentBet());
            $game->handleAction($player, $decision);
            $currentActionIndex++;
            $session->set('current_action_index', $currentActionIndex);
        }

        $currentActionIndex = $session->get('current_action_index', 0);

        // ensure numeric
        if (!is_numeric($currentActionIndex)) {
            $currentActionIndex = 0;
        }
        $currentActionIndex = (int) $currentActionIndex;
        $totalPlayers = count($game->getPlayersInOrder());
        $status = $this->gameHandlerService->advancePhaseIfNeeded($session, $game, $currentActionIndex, $totalPlayers);

        return $this->gameHandlerService->handleGameStatus($game, $status);

    }

    #[Route('/proj/submit-score', name: 'submit_score', methods: ['POST'])]
    public function submitScore(Request $request, SessionInterface $session): Response
    {
        // form data
        $username = $request->request->get('username', '');
        if (!is_string($username) || empty($username)) {
            return new JsonResponse(['error' => 'Invalid username.'], Response::HTTP_BAD_REQUEST);
        }

        $age = (int)$request->request->get('age');
        $scoreValue = (int)$request->request->get('score');

        // Submit the score
        $result = $this->scoreService->submitScore($username, $age, $scoreValue);

        if ($result->getStatusCode() !== Response::HTTP_OK) {
            return $result;
        }

        $game = $session->get('game');

        // Check if $game is an TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        // Render the game view
        return $this->render('texas/game.html.twig', [
            'game' => $game,
            'isGameOver' => $game->isGameOver(),
            'winners' => $game->getWinners(),
            'minChips' => $game->getMinimumChips(),
            'pot' => $game->getPotManager()->getPot(),
            'currentStage' => $game->getStageManager()->getCurrentStage(),
            'success_message' => 'Your score has been successfully submitted!'
        ]);
    }


    #[Route('/proj/new-round', name: 'proj_new_round', methods: ['POST'])]
    public function startNewRound(SessionInterface $session): Response
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        $game->startNewRound();

        $session->set('game', $game);

        return $this->redirectToRoute('proj_play');
    }

    #[Route('/proj/api', name: 'proj_api')]
    public function apiPage(): Response
    {
        return $this->render('texas/api.html.twig');
    }

}
