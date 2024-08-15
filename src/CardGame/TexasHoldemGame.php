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
    private WinnerEvaluator $winnerEvaluator;

    /** @var string[] */
    // Suppress the 'never read' warning for now as this property will be used later
    private array $actions = [];

    /** @var Player[] */
    private array $winners = [];

    private bool $gameOver = false;

    public function __construct()
    {
        $this->deck = new Deck();
        $this->potManager = new PotManager();
        $this->stageManager = new GameStageManager();
        $this->actionHandler = new PlayerActionHandler($this->potManager);
        $this->communityCardManager = new CommunityCardManager($this->deck);
        $this->winnerEvaluator = new WinnerEvaluator(new HandEvaluator());
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

    public function dealInitialCards(): void
    {
        foreach ($this->players as $player) {
            $card1 = $this->deck->drawCard();
            $card2 = $this->deck->drawCard();

            if ($card1 instanceof CardGraphic) {
                $player->addCardToHand($card1);
            }
            if ($card2 instanceof CardGraphic) {
                $player->addCardToHand($card2);
            }
        }
    }

    public function getMinimumChips(): int
    {
        $minChips = PHP_INT_MAX; // Set to maximum possible value initially
        foreach ($this->players as $player) {
            if (!$player->isFolded()) { // Consider only players who haven't folded
                $minChips = min($minChips, $player->getChips());
            }
        }
        return $minChips;
    }

    public function playRound(PlayerActionHandler $playerActionHandler, string $action, int $raiseAmount = 0): void
    {
        $this->processPlayerAction($action, $raiseAmount);
        $this->processComputerPlayers();
    
        // Automatically advance the game stages if the player is all-in
        if ($this->players[0]->getChips() === 0) {
            while (!$this->isGameOver()) {
                $this->advanceGameStage($playerActionHandler);
            }
            return; // Exit early, no need for further actions
        }
    
        $this->advanceGameStage($playerActionHandler);
    }
    

    private function processPlayerAction(string $action, int $raiseAmount): void
    {
        $player = $this->players[0];
        $action = $this->normalizeAction($player, $action, $raiseAmount);
        $this->handleAction($player, $action, $raiseAmount);
    }

    private function processComputerPlayers(): void
    {
        foreach (array_slice($this->players, 1) as $computerPlayer) { // Skipping the human player
            if ($computerPlayer->isFolded() || $computerPlayer->getChips() === 0) {
                continue;
            }
            $decision = $computerPlayer->makeDecision($this->communityCardManager->getCommunityCards(), $this->potManager->getCurrentBet());
            $this->handleAction($computerPlayer, $decision);
        }
    }

    private function normalizeAction(Player $player, string $action, int $raiseAmount): string
    {
        if ($action === 'raise' && $raiseAmount >= $player->getChips()) {
            return 'all-in';
        }
        return $action;
    }

    private function handleAction(Player $player, string $action, int $raiseAmount = 0): void
    {
        switch ($action) {
            case 'call':
                $this->actionHandler->handleCall($player);
                break;
            case 'raise':
                $this->actionHandler->handleRaise($player, $raiseAmount);
                break;
            case 'check':
                $this->actionHandler->handleCheck($player);
                break;
            case 'fold':
                $player->fold();
                break;
            case 'all-in':
                $this->actionHandler->handleAllIn($player);
                break;
        }

        // Update the actions array with the player's action
        $this->actions[$player->getName()] = ucfirst($action); // Convert action to a readable string
    }

    /**
     * @return string[] Array of player actions
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function resetCurrentBet(PlayerActionHandler $playerActionHandler): void
    {
        // Reset the current bet in the PotManager
        $this->potManager->resetCurrentBet();

        // Reset the current bet for all players using the PlayerActionHandler
        foreach ($this->players as $player) {
            $playerActionHandler->resetCurrentBet($player);
        }
    }

    private function advanceGameStage(PlayerActionHandler $playerActionHandler): void
    {
        if (count(array_filter($this->players, fn ($player) => !$player->isFolded())) == 1) {
            $this->determineWinner();
            return;
        }

        switch ($this->stageManager->getCurrentStage()) {
            case 0:
                $this->communityCardManager->dealCommunityCards(3); // Flop
                break;
            case 1:
                $this->communityCardManager->dealCommunityCards(1); // Turn
                break;
            case 2:
                $this->communityCardManager->dealCommunityCards(1); // River
                break;
            case 3:
                $this->determineWinner();
                break;
        }

        $this->stageManager->advanceStage();
        $this->resetCurrentBet($playerActionHandler);
    }

    private function determineWinner(): void
    {
        $remainingPlayers = array_filter($this->players, fn ($player) => !$player->isFolded());
        $this->winners = $this->winnerEvaluator->determineWinners($remainingPlayers, $this->communityCardManager->getCommunityCards());

        if (count($this->winners) > 1) {
            $this->splitPotAmongWinners();
            $this->gameOver = true; // Mark the game as over after splitting the pot
            return;
        }

        // If there is only one winner
        $this->winners[0]->addChips($this->potManager->getPot());
        $this->gameOver = true; // Mark the game as over
    }


    private function splitPotAmongWinners(): void
    {
        $potShare = intdiv($this->potManager->getPot(), count($this->winners));
        foreach ($this->winners as $winner) {
            $winner->addChips($potShare);
        }
    }

    public function startNewRound(PlayerActionHandler $playerActionHandler): void
    {
        $this->deck = new Deck();
        $this->communityCardManager->resetCommunityCards();
        $this->potManager->resetPot();
        $this->potManager->resetCurrentBet();
        $this->stageManager->resetStage();

        foreach ($this->players as $player) {
            if ($player->getChips() < 20) {
                $player->resetHand();
                $player->fold();
                $this->actions[$player->getName()] = "Player has insufficient chips, you'll need to start a new game.";
                continue;
            }
            $this->actions[$player->getName()] = 'No action yet';
            $player->resetHand();
            $playerActionHandler->resetCurrentBet($player);
            $player->unfold();
        }

        $this->gameOver = false;
        $this->winners = [];
        $this->dealInitialCards();
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
