<?php

namespace App\CardGame;

use App\CardGame\CardHand;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GameLogger
{
    public function logGameStart(SessionInterface $session, CardHand $playerHand, CardHand $dealerHand): string
    {
        $playerBet = $session->get('playerBet');
        $playerBetString = is_scalar($playerBet) ? (string)$playerBet : '';

        $gameLog = $session->get('gameLog') ?? '';
        $gameLog .= "\n\nStarted new round\nRegistered Bet as {$playerBetString}\n";
        $gameLog .= "Dealt cards to player: " . implode(', ', array_map(fn ($card) => "{$card->getCardName()} of {$card->getSuit()}", $playerHand->getCards())) . "\n";
        $gameLog .= "Dealt cards to dealer: " . implode(', ', array_map(fn ($card, $index) => $index === 1 ? '[Hidden Card]' : "{$card->getCardName()} of {$card->getSuit()}", $dealerHand->getCards(), array_keys($dealerHand->getCards()))) . "\n";

        $session->set('gameLog', $gameLog);
        return $gameLog;
    }

    public function updateGameLog(SessionInterface $session, string $message): void
    {
        $gameLog = $session->get('gameLog') ?? '';
        $gameLog .= $message;
        $session->set('gameLog', $gameLog);
    }
}
