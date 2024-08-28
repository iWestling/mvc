<?php

namespace App\CardGame;

use App\CardGame\Player;
use App\CardGame\PotManager;
use App\CardGame\TexasHoldemGame;

class PlayerActionHandler
{
    private PotManager $potManager;

    public function __construct(PotManager $potManager)
    {
        $this->potManager = $potManager;
    }

    /**
     * Process actions in order for each player.
     *
     * @param Player[] $playersInOrder Array of Player objects
     * @param string $playerAction Action taken by the human player
     * @param int $raiseAmount Amount raised by the human player
     * @param CommunityCardManager $communityCardManager Manager for community cards
     * @param callable $handleAction Callable to handle player actions
     */
    public function processActionsInOrder(array $playersInOrder, string $playerAction, int $raiseAmount, CommunityCardManager $communityCardManager, callable $handleAction): void
    {
        $currentBet = $this->potManager->getCurrentBet();

        foreach ($playersInOrder as $player) {
            if ($player->isFolded()) {
                continue;
            }

            if ($player->getName() === 'You') {
                $handleAction($player, $playerAction, $raiseAmount); // Human player action
                continue;
            }

            // Computer player's turn
            $decision = $player->makeDecision($communityCardManager->getCommunityCards(), $currentBet);
            $handleAction($player, $decision);
        }
    }

    public function processPlayerAction(Player $player, TexasHoldemGame $game, string $action, int $raiseAmount): void
    {
        // Skip folded player
        if ($player->isFolded()) {
            return;
        }

        $action = $this->normalizeAction($player, $action, $raiseAmount);
        $game->handleAction($player, $action, $raiseAmount);

        // If the player folded, exclude them from further actions
        if ($action === 'fold') {
            // Check if only one player remains
            if ($game->countActivePlayers() === 1) {
                $game->determineWinner();
                $game->setGameOver(true);
                return; // Exit to prevent further actions
            }

            // Move on to the next player's action automatically
            $this->processNextPlayerActions($game);
            return; // Exit to prevent further input for the folded player
        }

        if ($action === 'raise') {
            $this->processRaiseResponses($game);
        }
    }

    public function processNextPlayerActions(TexasHoldemGame $game): void
    {
        $playersInOrder = $game->getPlayersInOrder();
        $currentBet = $game->getPotManager()->getCurrentBet();
    
        foreach ($playersInOrder as $player) {
            if (!$player->isFolded() && $player->getChips() > 0) {
                $decision = $player->makeDecision($game->getCommunityCardManager()->getCommunityCards(), $currentBet);
                $game->handleAction($player, $decision);
    
                // If only one player remains, end the game
                if ($game->countActivePlayers() === 1) {
                    $game->determineWinner();
                    $game->setGameOver(true);
                    return;
                }
            }
        }
    
        // After all actions, automatically advance the game stage if needed
        if ($game->getPotManager()->haveAllActivePlayersMatchedCurrentBet($game->getPlayers())) {
            $game->advanceGameStage();
        }
    }
    
    public function processRaiseResponses(TexasHoldemGame $game): void
    {
        $currentBet = $game->getPotManager()->getCurrentBet();

        foreach ($game->getPlayers() as $player) {
            if (!$player->isFolded() && $player->getCurrentBet() < $currentBet) {
                $decision = $player->makeDecision($game->getCommunityCardManager()->getCommunityCards(), $currentBet);
                $game->handleAction($player, $decision);
            }
        }
    }
    public function handleRemainingPlayersAfterAllIn(TexasHoldemGame $game): void
    {
        $playersInOrder = $game->getPlayersInOrder();
        $currentBet = $game->getPotManager()->getCurrentBet();

        foreach ($playersInOrder as $player) {
            if ($player->isFolded() || $player->getChips() <= 0) {
                continue;
            }

            $decision = $player->makeDecision($game->getCommunityCardManager()->getCommunityCards(), $currentBet);

            // Ensure they call the All-In or fold, but cap the call amount to their chips
            if ($decision === 'call') {
                $callAmount = min($player->getChips(), $currentBet - $player->getCurrentBet());
                $game->handleAction($player, 'call', $callAmount);
                continue;
            }

            if ($decision === 'fold') {
                $game->handleAction($player, 'fold');
            }
        }
    }

    private function normalizeAction(Player $player, string $action, int $raiseAmount): string
    {
        if ($action === 'raise' && $raiseAmount >= $player->getChips()) {
            return 'all-in';
        }
        return $action;
    }
}
