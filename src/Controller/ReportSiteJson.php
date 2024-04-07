<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Card\DeckOfCards;

class ReportSiteJson
{
    #[Route("/api/lucky", name: "api_lucky_number")]
    public function jsonNumber(): Response
    {
        $number = random_int(0, 100);

        $data = [
            'lucky-number' => $number,
            'lucky-message' => 'Hi there!',
        ];

        // prettyprint
        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/quote", name: "api_quote")]
    public function jsonQuote(): JsonResponse
    {
        $quotes = [
            "Kom igen grabbar, dom har också en dålig målvakt! - Christer Abris",
            "Då sa jag till domaren på ren svenska, go home! - Christer Abris",
            "Jag är så grymt besviken på Börje, han är så jävla dålig - Peter Forsberg",
            "I owe a lot to my parents, especially by mother and my father. - Greg Norman",
            "They say that nobody is perfect. Then they tell you practice makes perfect. I wish they'd make up their minds. - Wilt Chamberlain",
            "Det är alldeles för mycket sport inom idrotten. - Percy Nilsson",
        ];

        $randomIndex = array_rand($quotes);
        $quote = $quotes[$randomIndex];

        $date = date("Y-m-d");
        $timestamp = time();

        $data = [
            'quote' => $quote,
            'date' => $date,
            'timestamp' => $timestamp
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
    #[Route("/api/deck", name: "api_deck", methods: ["GET"])]
    public function getDeck(SessionInterface $session): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // If the deck is not initialized, generate and store a new one in the session
        if (empty($deck)) {
            $deck = DeckOfCards::generateDeck();
            $session->set('deck', $deck);
        }

        // Convert deck to array of associative arrays for JSON response
        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'string' => $card->getAsString()
            ];
        }, $deck);

        // Create JSON response with pretty print
        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api_deck_shuffle", methods: ["POST"])]
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

        // Create JSON response with pretty print
        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
