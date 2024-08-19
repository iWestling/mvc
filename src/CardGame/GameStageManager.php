<?php

namespace App\CardGame;

class GameStageManager
{
    private int $stage = 0; // 0: Pre-Flop, 1: Flop, 2: Turn, 3: River

    public function getCurrentStage(): int
    {
        return $this->stage;
    }

    public function advanceStage(CommunityCardManager $communityCardManager): void
    {
        switch ($this->stage) {
            case 0:
                $communityCardManager->dealCommunityCards(3); // Flop
                break;
            case 1:
                $communityCardManager->dealCommunityCards(1); // Turn
                break;
            case 2:
                $communityCardManager->dealCommunityCards(1); // River
                break;
        }

        $this->stage++;
    }

    public function resetStage(): void
    {
        $this->stage = 0;
    }

    public function isFinalStage(): bool
    {
        return $this->stage >= 3; // After the River
    }
}
