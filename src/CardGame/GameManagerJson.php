<?php

namespace App\CardGame;

use Symfony\Component\HttpFoundation\JsonResponse;

class GameManagerJson
{
    public function startNewGame(int $chips, string $level1, string $level2): TexasHoldemGame
    {
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

        return $game;
    }

    // public function startNewRound(TexasHoldemGame $game): TexasHoldemGame
    // {
    //     if ($game instanceof TexasHoldemGame) {
    //         $game->startNewRound();
    //     }
    //     return $game;
    // }

    /**
     * @return array<string, mixed>
     */
    public function getGameState(TexasHoldemGame $game): array
    {
        return [
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
    }

    /**
     * @return array<string>
     */
    public function getCommunityCards(TexasHoldemGame $game): array
    {
        return array_map(fn ($card) => $card->getAsString(), $game->getCommunityCardManager()->getCommunityCards());
    }
}
