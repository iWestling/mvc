<?php

namespace App\CardGame;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GameDataService
{
    /**
     * @param SessionInterface $session
     * @param CardHand $playerHand
     * @param CardHand $dealerHand
     * @param array<string, int> $playerTotals
     * @param array<string, int> $dealerTotals
     * @param string $resultMessage
     * @return array<string, mixed>
     */
    public function getGameData(
        SessionInterface $session,
        CardHand $playerHand,
        CardHand $dealerHand,
        array $playerTotals,
        array $dealerTotals,
        string $resultMessage
    ): array {
        return [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $session->get('playerMoney'),
            'playerBet' => $session->get('playerBet'),
            'dealerMoney' => $session->get('dealerMoney'),
            'dealerUnturned' => $dealerHand->getCards()[1]->getAsString(),
            'playerTotalLow' => $playerTotals['low'],
            'playerTotalHigh' => $playerTotals['high'],
            'dealerTotalLow' => $dealerTotals['low'],
            'dealerTotalHigh' => $dealerTotals['high'],
            'resultMessage' => $resultMessage,
            'gameLog' => $session->get('gameLog'),
        ];
    }
}
