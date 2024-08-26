<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\CardGame\GameManagerJson;
use App\CardGame\PlayerManagerJson;
use App\CardGame\TexasHoldemGame;

class TexasHoldemJson extends AbstractController
{
    private GameManagerJson $gameManager;
    private PlayerManagerJson $playerManager;

    public function __construct(GameManagerJson $gameManager, PlayerManagerJson $playerManager)
    {
        $this->gameManager = $gameManager;
        $this->playerManager = $playerManager;
    }

    #[Route('/proj/api', name: 'proj_api')]
    public function apiPage(): Response
    {
        return $this->render('texas/api.html.twig');
    }


    #[Route('/proj/api/start-game', name: 'api_start_game', methods: ['POST'])]
    public function startNewGame(Request $request, SessionInterface $session): JsonResponse
    {
        $chips = (int)$request->request->get('chips', 1000);
        $level1 = (string)$request->request->get('level1', 'normal');
        $level2 = (string)$request->request->get('level2', 'normal');

        $game = $this->gameManager->startNewGame($chips, $level1, $level2);

        $session->set('game', $game);
        $session->set('current_action_index', 0); // Reset action index

        $response = new JsonResponse(['message' => 'New game started successfully.']);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    #[Route('/proj/api/game', name: 'api_game', methods: ['GET'])]
    public function getGameState(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        // Ensure $game is of type TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            $response = new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            return $response;
        }

        // Get the game state as an array
        $gameState = $this->gameManager->getGameState($game);

        // Create and return the JsonResponse
        $response = new JsonResponse($gameState);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    #[Route('/proj/api/reset-game', name: 'api_reset_game', methods: ['POST'])]
    public function resetGame(SessionInterface $session): JsonResponse
    {
        $session->remove('game');

        $response = new JsonResponse(['message' => 'Game reset successfully.']);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }


    #[Route('/proj/api/set-chips/{playerIndex}', name: 'api_set_chips', methods: ['POST'])]
    public function setChips(Request $request, SessionInterface $session, int $playerIndex): JsonResponse
    {
        $game = $session->get('game');

        // Ensure $game is of type TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            $response = new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            return $response;
        }

        $chips = (int)$request->request->get('chips', 1000);
        $result = $this->playerManager->setChips($game, $playerIndex, $chips);
        $response = new JsonResponse($result);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }


    #[Route('/proj/api/community-cards', name: 'api_community_cards', methods: ['GET'])]
    public function getCommunityCards(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        // Ensure $game is of type TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            $response = new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            return $response;
        }

        $communityCards = $this->gameManager->getCommunityCards($game);
        $response = new JsonResponse(['community_cards' => $communityCards]);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    #[Route('/proj/api/player-cards/{playerName}', name: 'api_player_cards', methods: ['GET'])]
    public function getPlayerCards(string $playerName, SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        // Ensure $game is of type TexasHoldemGame
        if (!$game instanceof TexasHoldemGame) {
            $response = new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
            return $response;
        }

        // Check if the player exists
        $players = $game->getPlayers();
        $player = null;
        foreach ($players as $p) {
            if ($p->getName() === $playerName) {
                $player = $p;
                break;
            }
        }

        if (!$player) {
            // Return a 404 status code if the player is not found
            return new JsonResponse(['error' => "Player with name $playerName not found"], 404);
        }

        // If the player is found, retrieve the player's cards
        $playerCards = $this->playerManager->getPlayerCards($game, $playerName);
        $response = new JsonResponse($playerCards);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

}
