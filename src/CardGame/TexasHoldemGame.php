<?php

namespace App\CardGame;

class TexasHoldemGame
{
    /** @var Player[] */
    private array $players = [];

    private CommunityCardManager $communityCardManager;
    private Deck $deck;
    private PotManager $potManager;
    private GameStageManager $stageManager;
    private PlayerActionHandler $actionHandler;
    private PlayerActionInit $actionInit;
    private WinnerEvaluator $winnerEvaluator;

    private bool $allInOccurred = false; // New flag for tracking All-In
    private bool $winnerDetermined = false; // Add this at the top of your class

    /** @var string[] */
    private array $actions = [];

    /** @var Player[] */
    private array $winners = [];

    private bool $gameOver = false;

    private int $dealerIndex = 0;  // Track the current dealer's index

    public function __construct()
    {
        $this->deck = new Deck();
        $this->potManager = new PotManager();
        $this->stageManager = new GameStageManager();
        $this->communityCardManager = new CommunityCardManager($this->deck);
        $this->winnerEvaluator = new WinnerEvaluator(new HandEvaluator());

        // Initialize action handler and action initializer
        $this->actionInit = new PlayerActionInit($this->potManager);
        $this->actionHandler = new PlayerActionHandler($this->potManager);
    }

    public function getHandEvaluator(): HandEvaluator
    {
        return $this->winnerEvaluator->getHandEvaluator();
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    // Getter for stageManager if needed outside the class
    public function getStageManager(): GameStageManager
    {
        return $this->stageManager;
    }


    public function getCommunityCardManager(): CommunityCardManager
    {
        return $this->communityCardManager;
    }

    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    public function getPotManager(): PotManager
    {
        return $this->potManager;
    }

    public function countActivePlayers(): int
    {
        return count(array_filter($this->players, fn ($player) => !$player->isFolded()));
    }

    public function hasAllInOccurred(): bool
    {
        return $this->allInOccurred;
    }

    public function setAllInOccurred(bool $allInOccurred): void
    {
        $this->allInOccurred = $allInOccurred;
    }
    public function setGameOver(bool $gameOver): void
    {
        $this->gameOver = $gameOver;
    }

    public function getMinimumChips(): int
    {
        $minChips = PHP_INT_MAX;
        foreach ($this->players as $player) {
            if (!$player->isFolded()) {
                $minChips = min($minChips, $player->getChips());
            }
        }
        return $minChips;
    }
    public function playRound(PlayerActionHandler $playerActionHandler, string $action, int $raiseAmount = 0): void
    {
        // Ensure blinds are handled at the start of the round
        $this->actionInit->handleBlinds($this->players);

        // Process all actions in the correct order
        $this->processActionsInOrder($playerActionHandler, $action, $raiseAmount);

        // If the first player is out of chips, advance the game stages until it ends
        if ($this->players[0]->getChips() === 0) {
            while (!$this->isGameOver()) {
                $this->advanceGameStage();
            }
            return;
        }

        // Automatically advance the game stages if all players have either folded or matched the current bet
        if ($this->potManager->haveAllActivePlayersMatchedCurrentBet($this->players) && !$this->isGameOver()) {
            $this->advanceGameStage();
        }
    }



    private function processActionsInOrder(PlayerActionHandler $playerActionHandler, string $playerAction, int $raiseAmount): void
    {
        $playersInOrder = $this->getPlayersInOrder();
        $playerActionHandler->processActionsInOrder($playersInOrder, $playerAction, $raiseAmount, $this->communityCardManager, [$this, 'handleAction']);
    }

    // public function processActionsInOrder(PlayerActionHandler $playerActionHandler, string $playerAction, int $raiseAmount): void
    // {
    //     $playersInOrder = $this->getPlayersInOrder();
    //     $currentBet = $this->potManager->getCurrentBet();

    //     dump("Processing actions in order. Current Bet: $currentBet");

    //     foreach ($playersInOrder as $player) {
    //         if ($player->isFolded()) {
    //             dump($player->getName() . " has folded. Skipping...");
    //             continue; // Skip folded players
    //         }

    //         dump("Processing action for: " . $player->getName());

    //         if ($player->getName() === 'You') {
    //             if ($player === reset($playersInOrder)) {
    //                 dump("You are processing your action: $playerAction with raise amount: $raiseAmount");
    //                 $this->processPlayerAction($playerAction, $raiseAmount);
    //             }
    //             continue;
    //         }

    //         // Computer player's turn
    //         $decision = $player->makeDecision($this->communityCardManager->getCommunityCards(), $currentBet);
    //         dump($player->getName() . " decision: " . ucfirst($decision));
    //         $this->handleAction($player, $decision);
    //     }

    //     if ($this->haveAllActivePlayersMatchedCurrentBet()) {
    //         dump("All active players have matched the current bet. Advancing to the next stage.");
    //         $this->advanceGameStage($playerActionHandler);
    //     }
    // }

    public function processPlayerAction(PlayerActionHandler $playerActionHandler, string $action, int $raiseAmount): void
    {
        $player = $this->players[0];
        $playerActionHandler->processPlayerAction($player, $this, $action, $raiseAmount);
    }

    /**
     * @return Player[]
     */
    public function getPlayersInOrder(): array
    {
        $orderedPlayers = [];
        $playerCount = count($this->players);

        for ($i = 0; $i < $playerCount; $i++) {
            $index = ($this->dealerIndex + $i) % $playerCount;
            $orderedPlayers[] = $this->players[$index];
        }

        return $orderedPlayers;
    }

    public function handleAction(Player $player, string $action, int $raiseAmount = 0): void
    {
        dump($player->getName() . " is taking action: " . ucfirst($action) . " with raise amount: $raiseAmount");

        switch ($action) {
            case 'call':
                $this->actionInit->handleCall($player);
                break;
            case 'raise':
                $this->actionInit->handleRaise($player, $raiseAmount);
                $this->potManager->updateCurrentBet($player->getCurrentBet());
                break;
            case 'check':
                $this->actionInit->handleCheck($player);
                break;
            case 'fold':
                $player->fold();
                break;
            case 'all-in':
                $this->actionInit->handleAllIn($player);
                $this->setAllInOccurred(true); // Set the flag when an All-In occurs

                // Handle actions for the remaining players
                $this->handleRemainingPlayersAfterAllIn($this->actionHandler);
                break;
        }

        // Log the player's new status after the action
        $status = $player->isFolded() ? "Folded" : "Active";
        dump($player->getName() . " status after action: " . $status);

        $this->actions[$player->getName()] = ucfirst($action);
    }


    public function handleRemainingPlayersAfterAllIn(PlayerActionHandler $playerActionHandler): void
    {
        $playerActionHandler->handleRemainingPlayersAfterAllIn($this);
    }



    /**
     * @return string[] Array of player actions
     */
    public function getActions(): array
    {
        return $this->actions;
    }


    public function advanceGameStage(): void
    {
        if ($this->gameOver) {
            return; // Prevent advancing stages after the game is over
        }

        // If only one player remains, end the game immediately
        if ($this->countActivePlayers() === 1) {
            $this->determineWinner();
            return;
        }

        // If it's not the final stage, advance the stage and deal community cards
        if (!$this->stageManager->isFinalStage()) {
            $this->stageManager->advanceStage($this->communityCardManager);
            $this->potManager->resetCurrentBet();
            return;
        }

        // If it's the final stage, handle the showdown and determine the winner
        dump("Showdown...");
        $this->determineWinner();
        $this->potManager->resetCurrentBet();
    }

    public function determineWinner(): void
    {
        if ($this->winnerDetermined) {
            return; // Prevent duplicate calls
        }

        dump("determineWinner() called");
        $remainingPlayers = array_filter($this->players, fn ($player) => !$player->isFolded());

        if (count($remainingPlayers) === 1) {
            $this->winners = $remainingPlayers;
            $this->finalizeWinners();
            return;
        }

        $this->winners = $this->winnerEvaluator->determineWinners($remainingPlayers, $this->communityCardManager->getCommunityCards());

        if (empty($this->winners)) {
            dump('No winners found in the game, something went wrong.');
            return;
        }

        $this->finalizeWinners();
    }

    private function finalizeWinners(): void
    {
        if (count($this->winners) > 1) {
            $this->potManager->splitPotAmongWinners($this->winners);
            $this->finalizeGame();
            return;
        }

        $winner = reset($this->winners);
        if ($winner instanceof Player) {
            $this->potManager->distributeWinningsToPlayer($winner);
        }

        $this->finalizeGame();
    }


    private function finalizeGame(): void
    {
        $this->gameOver = true;
        $this->winnerDetermined = true;
    }



    // private function distributeWinningsToPlayer(Player $player, int $amount): void
    // {
    //     dump("Before distributing, " . $player->getName() . " has " . $player->getChips() . " chips");
    //     dump("Distributing $amount chips to " . $player->getName());

    //     $player->addChips($amount); // Add the pot amount to the winner's chips

    //     dump("After distributing, " . $player->getName() . " now has " . $player->getChips() . " chips");

    //     // // Reset the pot AFTER distributing the winnings
    //     // $this->potManager->resetPot();
    // }



    // private function splitPotAmongWinners(): void
    // {
    //     $potShare = intdiv($this->potManager->getPot(), count($this->winners));
    //     foreach ($this->winners as $winner) {
    //         // Distribute only the calculated share to each winner
    //         $this->distributeWinningsToPlayer($winner, $potShare);
    //     }
    // }

    public function startNewRound(): void
    {
        $this->winnerDetermined = false;
        $this->deck = new Deck();
        $this->communityCardManager->resetCommunityCards();
        $this->potManager->resetPot();
        $this->potManager->resetCurrentBet();
        $this->stageManager->resetStage();

        // Rotate roles before starting a new round
        $this->actionInit->rotateRoles($this->dealerIndex, count($this->players));
        $this->actionInit->initializeRoles($this->players, $this->dealerIndex);

        // Reset player states and handle blinds
        $this->actionInit->resetPlayersForNewRound($this->players, $this->actions);  // Reset player states for the new round
        $this->actionInit->handleBlinds($this->players);  // Handle blinds (small blind, big blind)


        $this->actionInit->handleBlinds($this->players);

        $this->gameOver = false;
        $this->allInOccurred = false;
        $this->winners = [];
        $this->communityCardManager->dealInitialCards($this->players);
    }

    public function isGameOver(): bool
    {
        return $this->gameOver;
    }

    /**
     * @return Player[]
     */
    public function getWinners(): array
    {
        return $this->winners;
    }
}
