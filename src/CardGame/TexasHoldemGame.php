<?php

namespace App\CardGame;

class TexasHoldemGame
{
    private Deck $deck;
    private array $players = [];
    private array $communityCards = [];
    private int $pot = 0;
    private int $currentBet = 0;
    private int $stage = 0; // 0: Pre-Flop, 1: Flop, 2: Turn, 3: River
    private Bank $bank;
    private array $actions = [];
    private bool $gameOver = false;
    private array $winners = [];

    public function __construct()
    {
        $this->deck = new Deck();
        $this->bank = new Bank();
    }

    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    public function dealInitialCards(): void
    {
        foreach ($this->players as $player) {
            $player->addCardToHand($this->deck->drawCard());
            $player->addCardToHand($this->deck->drawCard());
        }
    }

    public function dealCommunityCards(int $number): void
    {
        for ($i = 0; $i < $number; $i++) {
            $this->communityCards[] = $this->deck->drawCard();
        }
    }

    public function getCommunityCards(): array
    {
        return $this->communityCards;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getPot(): int
    {
        return $this->pot;
    }

    public function getCurrentBet(): int
    {
        return $this->currentBet;
    }

    public function resetCurrentBet(): void
    {
        $this->currentBet = 0;
        foreach ($this->players as $player) {
            $player->resetCurrentBet();
        }
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function playRound(string $action, int $raiseAmount = 0): void
    {
        $player = $this->players[0];

        // Handle player's action
        switch ($action) {
            case 'call':
                $this->handleCall($player);
                $this->actions[$player->getName()] = 'call';
                break;
            case 'raise':
                $this->handleRaise($player, $raiseAmount);
                $this->actions[$player->getName()] = 'raise';
                break;
            case 'check':
                $this->handleCheck($player);
                $this->actions[$player->getName()] = 'check';
                break;
            case 'fold':
                $player->fold();
                $this->actions[$player->getName()] = 'fold';
                break;
        }

        // Handle computer players' actions
        foreach (array_slice($this->players, 1) as $computerPlayer) {
            if ($computerPlayer->isFolded()) {
                $this->actions[$computerPlayer->getName()] = 'fold';
                continue;
            }
            $decision = $computerPlayer->makeDecision($this->communityCards, $this->currentBet);
            switch ($decision) {
                case 'call':
                    $this->handleCall($computerPlayer);
                    $this->actions[$computerPlayer->getName()] = 'call';
                    break;
                case 'raise':
                    $this->handleRaise($computerPlayer, 20); // Example raise amount
                    $this->actions[$computerPlayer->getName()] = 'raise';
                    break;
                case 'check':
                    $this->handleCheck($computerPlayer);
                    $this->actions[$computerPlayer->getName()] = 'check';
                    break;
                case 'fold':
                    $computerPlayer->fold();
                    $this->actions[$computerPlayer->getName()] = 'fold';
                    break;
            }
        }

        // Move to the next stage of community cards
        $this->advanceGameStage();
    }

    private function advanceGameStage(): void
    {
        if (count(array_filter($this->players, fn($player) => !$player->isFolded())) == 1) {
            $this->determineWinner(); // End game if only one player remains
            return;
        }

        if ($this->stage == 0) {
            $this->dealCommunityCards(3); // Flop
        } elseif ($this->stage == 1) {
            $this->dealCommunityCards(1); // Turn
        } elseif ($this->stage == 2) {
            $this->dealCommunityCards(1); // River
        } elseif ($this->stage == 3) {
            $this->determineWinner(); // End game
        }

        $this->stage++;
        $this->resetCurrentBet();
    }

    private function handleCall(Player $player): void
    {
        $player->call($this->currentBet);
        $this->pot += $player->getCurrentBet();
    }

    private function handleRaise(Player $player, int $amount): void
    {
        $player->raise($amount);
        $this->currentBet = $amount;
        $this->pot += $player->getCurrentBet();
    }

    private function handleCheck(Player $player): void
    {
        $player->check();
    }

    private function determineWinner(): void
    {
        $remainingPlayers = array_filter($this->players, fn($player) => !$player->isFolded());
        $bestHand = ['rank' => '', 'values' => []]; // Initialize with a valid structure
        $this->winners = [];

        foreach ($remainingPlayers as $player) {
            $hand = array_merge($player->getHand(), $this->communityCards);
            $bestPlayerHand = HandEvaluator::getBestHand($hand);

            if (empty($bestHand['rank']) || HandEvaluator::compareHands($bestPlayerHand, $bestHand) > 0) {
                $bestHand = $bestPlayerHand;
                $this->winners = [$player];
            } elseif (HandEvaluator::compareHands($bestPlayerHand, $bestHand) == 0) {
                $this->winners[] = $player;
            }
        }

        if (count($this->winners) > 1) {
            // Handle tie: split the pot among winners
            $potShare = intdiv($this->pot, count($this->winners));
            foreach ($this->winners as $winner) {
                $winner->addChips($potShare);
            }
        } else {
            $this->winners[0]->addChips($this->pot);
        }

        // Mark the game as over
        $this->gameOver = true;
    }

    public function isGameOver(): bool
    {
        return $this->gameOver;
    }

    public function getWinners(): array
    {
        return $this->winners;
    }

    private function resetGame(): void
    {
        $this->deck = new Deck();
        $this->communityCards = [];
        $this->pot = 0;
        $this->currentBet = 0;
        $this->stage = 0;
        foreach ($this->players as $player) {
            $player->resetHand();
            $player->resetCurrentBet();
            $player->unfold();
        }
        $this->actions = [];
        $this->gameOver = false;
        $this->winners = [];
    }
}
