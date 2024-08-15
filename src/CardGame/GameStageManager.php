<?php

namespace App\CardGame;

class GameStageManager
{
    private int $stage = 0; // 0: Pre-Flop, 1: Flop, 2: Turn, 3: River

    public function getCurrentStage(): int
    {
        return $this->stage;
    }

    public function advanceStage(): void
    {
        $this->stage++;
    }

    public function resetStage(): void
    {
        $this->stage = 0;
    }
}
