<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Card\DeckOfCards;

class CardGameControllerJson
{
    #[Route("/api/deck", name: "api_card_deck", methods: ["GET"])]
    public function getDeck(SessionInterface $session): JsonResponse
    {
        // Generate a new deck of cards
        $deck = DeckOfCards::generateDeck();

        // Convert deck to array of associative arrays for JSON response
        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'string' => $card->getAsString()
            ];
        }, $deck);

        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api_card_shuffle", methods: ["POST"])]
    public function shuffleDeck(Request $request, SessionInterface $session): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // Shuffle the deck
        shuffle($deck);

        // Store the shuffled deck back in the session
        $session->set('deck', $deck);

        // Convert deck to array of associative arrays for JSON response
        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'string' => $card->getAsString()
            ];
        }, $deck);

        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
}
