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
use App\CardGame\PlayerActionInit;

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

            // Initialize a new Texas Hold'em game
            $game = new TexasHoldemGame();
            $game->addPlayer(new Player('You', $chips, 'intelligent')); // Human player
            $game->addPlayer(new Player('Computer 1', $chips, $level1)); // Computer 1
            $game->addPlayer(new Player('Computer 2', $chips, $level2)); // Computer 2

            // Initialize PlayerActionInit
            $potManager = $game->getPotManager();
            $playerActionInit = new PlayerActionInit($potManager);

            // Initialize roles for the first round (Dealer, Small Blind, Big Blind)
            $playerActionInit->initializeRoles($game->getPlayers(), 0); // Assuming dealer starts at index 0

            // Deal initial cards to the players using CommunityCardManager
            $game->getCommunityCardManager()->dealInitialCards($game->getPlayers());

            // Process blinds (Small Blind and Big Blind)
            $playerActionInit->handleBlinds($game->getPlayers());

            // Save the game state to the session
            $session->set('game', $game);
            $session->set('current_action_index', 0); // Reset action index

            // Redirect to the play route to start the game
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

        $playerActionHandler = new PlayerActionHandler($game->getPotManager());

        // Handle the case where an All-In has occurred
        if ($this->handleAllInScenario($game, $playerActionHandler)) {
            // If the game has ended after handling the All-In scenario, render the final state using renderGameView
            return $this->renderGameView($game);
        }

        // Normal flow if no All-In occurred
        $playersInOrder = $game->getPlayersInOrder();
        $currentActionIndex = $session->get('current_action_index', 0);
        // Ensure $currentActionIndex is a valid integer
        if (!is_numeric($currentActionIndex)) {
            $currentActionIndex = 0;  // Set to default if not numeric
        }
        $currentActionIndex = (int) $currentActionIndex;  // Cast to integer
        $totalPlayers = count($playersInOrder);  // Cache the result of count()

        // Handle the current player's action
        while ($currentActionIndex < $totalPlayers) {
            $player = $playersInOrder[$currentActionIndex];

            // If the player is folded, skip their turn
            if ($player->isFolded()) {
                $currentActionIndex++;
                continue;
            }

            // If it's the human player's turn, process their action via form submission
            if ($player->getName() === 'You') {
                if ($request->isMethod('POST')) {
                    $action = (string)$request->request->get('action', 'check');
                    $raiseAmount = (int)$request->request->get('raiseAmount', 0);

                    $game->processPlayerAction($playerActionHandler, $action, $raiseAmount);
                    $currentActionIndex++;
                    $session->set('current_action_index', $currentActionIndex);
                    return $this->redirectToRoute('proj_play');  // Redirect to update the page after the human player's action
                }

                // Break here because we need to wait for the human player's input
                break;
            }

            // If it's a computer player's turn, automatically process their action
            $decision = $player->makeDecision($game->getCommunityCardManager()->getCommunityCards(), $game->getPotManager()->getCurrentBet());
            $game->handleAction($player, $decision);

            // Move to the next player's turn
            $currentActionIndex++;
            $session->set('current_action_index', $currentActionIndex);
        }

        // Check if all players have acted and if it's time to advance the phase
        $response = $this->advancePhaseIfNeeded($session, $game, $currentActionIndex, $totalPlayers);

        // If no response was returned from advancePhaseIfNeeded, render the game view
        if ($response === null) {
            return $this->renderGameView($game);
        }

        return $response;
    }

    private function advancePhaseIfNeeded(SessionInterface $session, TexasHoldemGame $game, int $currentActionIndex, int $totalPlayers): ?Response
    {
        if ($currentActionIndex >= $totalPlayers && $game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
            // If all players have matched the current bet, advance to the next phase
            $game->advanceGameStage();  // This now uses the refactored `advanceGameStage` method

            // Reset the action index for the next phase
            $session->set('current_action_index', 0);

            // Check if the game is over and render the final state if it is
            if ($game->isGameOver()) {
                return $this->renderGameView($game);  // Reuse renderGameView for rendering
            }

            return $this->redirectToRoute('proj_play');  // Redirect to start the next phase
        }

        return null;  // Return null if no phase advancement is needed
    }
    private function renderGameView(TexasHoldemGame $game): Response
    {
        return $this->render('texas/game.html.twig', [
            'game' => $game,
            'isGameOver' => $game->isGameOver(),
            'winners' => $game->getWinners(),
            'minChips' => $game->getMinimumChips(),
            'pot' => $game->getPotManager()->getPot(),  // Pass the current pot value
        ]);
    }
    /**
     * Handle the scenario when an All-In has occurred.
     */
    private function handleAllInScenario(TexasHoldemGame $game, PlayerActionHandler $playerActionHandler): bool
    {
        // Check if an All-In has occurred, and if so, ensure all players have acted
        if ($game->hasAllInOccurred()) {
            // Ensure remaining players have had a chance to call or fold
            $game->handleRemainingPlayersAfterAllIn($playerActionHandler);

            // After all players have acted, proceed with the game stages
            while (!$game->isGameOver()) {
                $game->advanceGameStage();  // This now uses the refactored `advanceGameStage` method
            }

            return true;  // Game has ended after the All-In scenario
        }

        return false;  // No All-In scenario handled
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

}
