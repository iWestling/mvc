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
        if ($dealerHand !== null && count($dealerHand->getCards()) > 0) {
            $dealerUnturned = $dealerHand->getCards()[0]->getUnturned();
        } else {
            $response = [
                'message' => 'Please start a game.'
            ];
            $response = new JsonResponse($response);
            $response->setEncodingOptions(
                $response->getEncodingOptions() | JSON_PRETTY_PRINT
            );
            return $response;
        }
    
        // Calculate total score for player and dealer
        $playerTotals = $playerHand ? $playerHand->calculateTotal() : ['low' => 0, 'high' => 0];
        $playerTotalLow = $playerTotals['low'];
        $playerTotalHigh = $playerTotals['high'];
    
        $dealerTotals = $dealerHand ? $dealerHand->calculateTotalDealer() : ['low' => 0, 'high' => 0];
        $dealerTotalLow = $dealerTotals['low'];
        $dealerTotalHigh = $dealerTotals['high'];
    
        $gameLog = $session->get('gameLog');
    
        $response = [
            'playerHand' => $playerHand ? array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()) : [],
            'dealerHand' => $dealerHand ? array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()) : [],
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
