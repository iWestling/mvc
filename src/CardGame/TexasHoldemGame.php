<?php

namespace App\CardGame;

use RuntimeException;

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

    private bool $allInOccurred = false;
    private bool $winnerDetermined = false;

    /** @var string[] */
    private array $actions = [];

    /** @var Player[] */
    private array $winners = [];

    private bool $gameOver = false;

    private int $dealerIndex = 0;

    public function __construct()
    {
        $this->deck = new Deck();
        $this->potManager = new PotManager();
        $this->stageManager = new GameStageManager();
        $this->communityCardManager = new CommunityCardManager($this->deck);
        $this->winnerEvaluator = new WinnerEvaluator(new HandEvaluator());

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
        // blinds handled at the start of the round
        $this->actionInit->handleBlinds($this->players);

        // Process all actions in correct order
        $this->processActionsInOrder($playerActionHandler, $action, $raiseAmount);

        // If the first player is out of chips, advance the game stages until it ends
        if ($this->players[0]->getChips() === 0) {
            while (!$this->isGameOver()) {
                $this->advanceGameStage();
            }
            return;
        }

        // Automatically advance the game stages if all players have folded or matched current bet
        if ($this->potManager->haveAllActivePlayersMatchedCurrentBet($this->players) && !$this->isGameOver()) {
            $this->advanceGameStage();
        }
    }



    private function processActionsInOrder(PlayerActionHandler $playerActionHandler, string $playerAction, int $raiseAmount): void
    {
        $playersInOrder = $this->getPlayersInOrder();
        $playerActionHandler->processActionsInOrder($playersInOrder, $playerAction, $raiseAmount, $this->communityCardManager, [$this, 'handleAction']);
    }

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
        // Check if the player is a computer and if the action is "raise"
        if ($action === 'raise' && strpos($player->getName(), 'Computer') !== false) {
            // Set the raise amount for computer player
            $raiseAmount = 10;
        }

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

        $this->actions[$player->getName()] = ucfirst($action);
        if ($this->countActivePlayers() === 1) {
            $this->determineWinner();
            $this->setGameOver(true);
        }
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

        // If only one player remains, end the game
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

        // if it's the final stage, handle showdown and determine winner
        dump("Showdown...");
        $this->determineWinner();
        $this->potManager->resetCurrentBet();
    }

    public function determineWinner(): void
    {
        if ($this->winnerDetermined) {
            return; // Prevent duplicates
        }

        $remainingPlayers = array_filter($this->players, fn ($player) => !$player->isFolded());

        if (count($remainingPlayers) === 1) {
            $this->winners = $remainingPlayers;
            $this->finalizeWinners();
            return;
        }

        $this->winners = $this->winnerEvaluator->determineWinners($remainingPlayers, $this->communityCardManager->getCommunityCards());

        if (empty($this->winners)) {
            throw new RuntimeException('No winners found in the game, something went wrong.');
        }

        $this->finalizeWinners();
    }

    private function finalizeWinners(): void
    {
        if (count($this->winners) > 1) {
            $this->potManager->splitPotAmongWinners($this->winners);
            $this->gameOver = true;
            $this->winnerDetermined = true;
            return;
        }

        $winner = reset($this->winners);
        if ($winner instanceof Player) {
            $this->potManager->distributeWinningsToPlayer($winner);
        }

        $this->gameOver = true;
        $this->winnerDetermined = true;
    }


    public function startNewRound(): void
    {

        $this->deck = new Deck();
        $this->communityCardManager = new CommunityCardManager($this->deck);
        $this->communityCardManager->resetCommunityCards();
        $this->winnerDetermined = false;
        $this->potManager->resetPot();
        $this->potManager->resetCurrentBet();
        $this->stageManager->resetStage();

        // Rotate roles before starting a new round
        $this->actionInit->rotateRoles($this->dealerIndex, count($this->players));
        $this->actionInit->initializeRoles($this->players, $this->dealerIndex);

        // Reset player states and handle blinds
        $this->actionInit->resetPlayersForNewRound($this->players, $this->actions);
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
