<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\CardGame\CardHand;

class BlackJackJson
{
    #[Route("/api/game", name: "api_game", methods: ['GET'])]
    public function play(
        SessionInterface $session
    ): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        /** @var CardHand|null $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand|null $dealerHand */
        $dealerHand = $session->get('dealerHand');

        // Prepare unturned card for dealer
        if ($dealerHand === null || count($dealerHand->getCards()) === 0) {
            $response = [
                'message' => 'Please start a game.'
            ];
            $response = new JsonResponse($response);
            $response->setEncodingOptions(
                $response->getEncodingOptions() | JSON_PRETTY_PRINT
            );
            return $response;
        }

        $dealerUnturned = $dealerHand->getCards()[0]->getUnturned();
        $playerTotalLow = 0;
        $playerTotalHigh = 0;
        $dealerTotalLow = 0;
        $dealerTotalHigh = 0;
        // Calculate total score for player and dealer
        if ($playerHand !== null) {
            $playerTotals = $playerHand->calculateTotal();
            $playerTotalLow = $playerTotals['low'];
            $playerTotalHigh = $playerTotals['high'];
        }

        if ($dealerHand !== null) {
            $dealerTotals = $dealerHand->calculateTotalDealer();
            $dealerTotalLow = $dealerTotals['low'];
            $dealerTotalHigh = $dealerTotals['high'];
        }

        $dealerHandResponse = [];

        if ($dealerHand !== null) {
            $dealerHandResponse = array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards());
        }

        $gameLog = $session->get('gameLog');

        $response = [
            'playerHand' => $playerHand ? array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()) : [],
            'dealerHand' => $dealerHandResponse,
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerUnturned,
            'playerTotalLow' => $playerTotalLow,
            'playerTotalHigh' => $playerTotalHigh,
            'dealerTotalLow' => $dealerTotalLow,
            'dealerTotalHigh' => $dealerTotalHigh,
            'gameLog' => $gameLog,
        ];

        $response = new JsonResponse($response);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
