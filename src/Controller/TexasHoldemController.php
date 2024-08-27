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
        // Get form data
        $username = $request->request->get('username', '');
        if (!is_string($username) || empty($username)) {
            return new JsonResponse(['error' => 'Invalid username.'], Response::HTTP_BAD_REQUEST);
        }

        $age = (int)$request->request->get('age');
        $scoreValue = (int)$request->request->get('score');

        // Submit the score using the ScoreService
        $result = $this->scoreService->submitScore($username, $age, $scoreValue);

        // If the result is not a success, return the JsonResponse from the service
        if ($result->getStatusCode() !== Response::HTTP_OK) {
            return $result;
        }

        // Retrieve the game from the session
        $game = $session->get('game');

        // Check if $game is an instance of TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            return $this->redirectToRoute('proj_start');
        }

        // Render the game view with a success message
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

    // private function advancePhaseIfNeeded(SessionInterface $session, TexasHoldemGame $game, int $currentActionIndex, int $totalPlayers): ?Response
    // {
    //     if ($currentActionIndex >= $totalPlayers && $game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
    //         // If all players have matched the current bet, advance to the next phase
    //         $game->advanceGameStage();  // This now uses the refactored `advanceGameStage` method

    //         // Reset the action index for the next phase
    //         $session->set('current_action_index', 0);

    //         // Check if the game is over and render the final state if it is
    //         if ($game->isGameOver()) {
    //             return $this->renderGameView($game);  // Reuse renderGameView for rendering
    //         }

    //         return $this->redirectToRoute('proj_play');  // Redirect to start the next phase
    //     }

    //     return null;  // Return null if no phase advancement is needed
    // }
    // private function renderGameView(TexasHoldemGame $game): Response
    // {
    //     // Render the game view with a success message
    //     return $this->render('texas/game.html.twig', [
    //         'game' => $game,
    //         'isGameOver' => $game->isGameOver(),
    //         'winners' => $game->getWinners(),
    //         'minChips' => $game->getMinimumChips(),
    //         'pot' => $game->getPotManager()->getPot(),
    //         'currentStage' => $game->getStageManager()->getCurrentStage(),
    //         'success_message' => 'Your score has been successfully submitted!'
    //     ]);
    // }
    // /**
    //  * Handle the scenario when an All-In has occurred.
    //  */
    // private function handleAllInScenario(TexasHoldemGame $game, PlayerActionHandler $playerActionHandler): bool
    // {
    //     // Check if an All-In has occurred, and if so, ensure all players have acted
    //     if ($game->hasAllInOccurred()) {
    //         // Ensure remaining players have had a chance to call or fold
    //         $game->handleRemainingPlayersAfterAllIn($playerActionHandler);

    //         // After all players have acted, proceed with the game stages
    //         while (!$game->isGameOver()) {
    //             $game->advanceGameStage();  // This now uses the refactored `advanceGameStage` method
    //         }

    //         return true;  // Game has ended after the All-In scenario
    //     }

    //     return false;  // No All-In scenario handled
    // }

}
