<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\CardGame\TexasHoldemGame;
use App\CardGame\Player;
use App\CardGame\PlayerActionHandler;

class TexasHoldemJson extends AbstractController
{
    #[Route('/proj/api', name: 'proj_api')]
    public function apiPage(): Response
    {
        return $this->render('texas/api.html.twig');
    }

    #[Route('/proj/api/start-game', name: 'api_start_game', methods: ['POST'])]
    public function startNewGame(Request $request, SessionInterface $session): JsonResponse
    {
        // Get player settings from the request (with defaults)
        $chips = (int)$request->request->get('chips', 1000);
        $level1 = (string)$request->request->get('level1', 'normal');
        $level2 = (string)$request->request->get('level2', 'normal');

        // Initialize a new Texas Hold'em game
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', $chips, 'intelligent')); // Human player
        $game->addPlayer(new Player('Computer 1', $chips, $level1)); // Computer 1
        $game->addPlayer(new Player('Computer 2', $chips, $level2)); // Computer 2

        // Save the game state to the session
        $session->set('game', $game);

        return new JsonResponse(['message' => 'New game started successfully.'], 200);
    }

    #[Route('/proj/api/game', name: 'api_game', methods: ['GET'])]
    public function getGameState(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $responseData = [
            'players' => array_map(fn ($player) => [
                'name' => $player->getName(),
                'chips' => $player->getChips(),
                'hand' => array_map(fn ($card) => $card->getAsString(), $player->getHand()),
                'folded' => $player->isFolded(),
                'current_bet' => $player->getCurrentBet(),
            ], $game->getPlayers()),
            'community_cards' => array_map(fn ($card) => $card->getAsString(), $game->getCommunityCardManager()->getCommunityCards()),
            'pot' => $game->getPotManager()->getPot(),
            'stage' => $game->getStageManager()->getCurrentStage(),
            'game_over' => $game->isGameOver(),
            'winners' => array_map(fn ($winner) => $winner->getName(), $game->getWinners()),
        ];

        // Create JsonResponse and set encoding options
        $response = new JsonResponse($responseData);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }


    #[Route('/proj/api/new-round', name: 'api_new_round', methods: ['POST'])]
    public function startNewRound(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $game->startNewRound();
        $session->set('game', $game);

        return new JsonResponse(['message' => 'New round started.'], 200);
    }

    #[Route('/proj/api/reset-game', name: 'api_reset_game', methods: ['POST'])]
    public function resetGame(SessionInterface $session): JsonResponse
    {
        $session->remove('game');
        return new JsonResponse(['message' => 'Game reset successfully.'], 200);
    }

    #[Route('/proj/api/set-chips/you', name: 'api_set_chips_you', methods: ['POST'])]
    public function setChips(Request $request, SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $chips = (int)$request->request->get('chips', 1000);
        $player = $game->getPlayers()[0]; // Assuming the first player is the human player
        $player->setChips($chips);

        return new JsonResponse(['message' => 'Your chips set successfully.'], 200);
    }

    #[Route('/proj/api/set-chips/comp1', name: 'api_set_chips_comp_one', methods: ['POST'])]
    public function setChipsCompOne(Request $request, SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $chips = (int)$request->request->get('chips', 1000);
        $player = $game->getPlayers()[1]; // Assuming the second player is Computer 1
        $player->setChips($chips);

        return new JsonResponse(['message' => 'Computer 1 chips set successfully.'], 200);
    }

    #[Route('/proj/api/set-chips/comp2', name: 'api_set_chips_comp_two', methods: ['POST'])]
    public function setChipsCompTwo(Request $request, SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $chips = (int)$request->request->get('chips', 1000);
        $player = $game->getPlayers()[2]; // Assuming the third player is Computer 2
        $player->setChips($chips);

        return new JsonResponse(['message' => 'Computer 2 chips set successfully.'], 200);
    }

    #[Route('proj/api/community-cards', name: 'api_community_cards', methods: ['GET'])]
    public function getCommunityCards(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $communityCards = array_map(fn ($card) => $card->getAsString(), $game->getCommunityCardManager()->getCommunityCards());

        // Create JsonResponse and set encoding options
        $response = new JsonResponse(['community_cards' => $communityCards]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }


    #[Route('proj/api/player-cards/{playerName}', name: 'api_player_cards', methods: ['GET'])]
    public function getPlayerCards(SessionInterface $session, string $playerName): JsonResponse
    {
        $game = $session->get('game');

        if (!$game instanceof TexasHoldemGame) {
            return new JsonResponse(['error' => 'No game found. Start a new game first.'], 404);
        }

        $player = null;
        foreach ($game->getPlayers() as $p) {
            if ($p->getName() === $playerName) {
                $player = $p;
                break;
            }
        }

        if (!$player) {
            return new JsonResponse(['error' => "Player with name $playerName not found."], 404);
        }

        $playerCards = array_map(fn ($card) => $card->getAsString(), $player->getHand());

        // Create JsonResponse and set encoding options
        $response = new JsonResponse(['player_name' => $playerName, 'cards' => $playerCards]);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

}
