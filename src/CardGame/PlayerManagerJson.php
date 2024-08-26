<?php

namespace App\CardGame;

class PlayerManagerJson
{
    /**
     * @return array<string, string>
     */
    public function setChips(TexasHoldemGame $game, int $playerIndex, int $chips): array
    {
        $players = $game->getPlayers();
        if (!isset($players[$playerIndex])) {
            return ['error' => 'Invalid player index'];
        }

        $players[$playerIndex]->setChips($chips);
        return ['message' => 'Player chips set successfully'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPlayerCards(TexasHoldemGame $game, string $playerName): array
    {
        $player = null;
        foreach ($game->getPlayers() as $p) {
            if ($p->getName() === $playerName) {
                $player = $p;
                break;
            }
        }

        if (!$player) {
            return ['error' => "Player with name $playerName not found"];
        }

        $playerCards = array_map(fn ($card) => $card->getAsString(), $player->getHand());

        return ['player_name' => $playerName, 'cards' => $playerCards];
    }

}
