<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Card\DeckOfCards;
use App\Card\CardHand;

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
                'string' => $card->getCardAsString()
            ];
        }, $deck);

        // Create JSON response with pretty print
        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api_deck_shuffle", methods: ["GET", "POST"])]
    public function shuffleDeck(SessionInterface $session): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // If the deck is not initialized, generate and store a new one in the session
        if (empty($deck)) {
            $deck = DeckOfCards::generateDeck();
            $session->set('deck', $deck);
        }

        // Shuffle the deck
        shuffle($deck);

        // Store the shuffled deck back into the session
        $session->set('deck', $deck);

        // Convert deck to array of associative arrays for JSON response
        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'string' => $card->getCardAsString()
            ];
        }, $deck);

        // Create JSON response with pretty print
        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/draw", name: "api_deck_draw", methods: ["GET", "POST"])]
    public function drawCardFromDeck(SessionInterface $session): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // If the deck is not initialized or empty, return an error response
        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        // Draw a single card from the deck
        $drawnCard = array_shift($deck);

        // Update the deck in the session
        $session->set('deck', $deck);

        // Prepare the response JSON structure
        $response = [
            'drawnCard' => [
                'value' => $drawnCard->getValue(),
                'string' => $drawnCard->getCardAsString()
            ],
            'remainingCards' => count($deck)
        ];

        // Return the JSON response
        return new JsonResponse($response);
    }

    #[Route("/api/deck/draw/{number}", name: "api_deck_draw_multiple", methods: ["GET", "POST"])]
    public function drawMultipleCardsFromDeck(Request $request, SessionInterface $session, int $number): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // If the deck is not initialized or empty, return an error response
        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the number of cards to draw
        if ($number <= 0) {
            return new JsonResponse(['error' => 'Invalid number of cards to draw.'], Response::HTTP_BAD_REQUEST);
        }

        // Draw the specified number of cards from the deck
        $drawnCards = [];
        for ($i = 0; $i < $number; $i++) {
            if (!empty($deck)) {
                $drawnCard = array_shift($deck);
                $drawnCards[] = [
                    'value' => $drawnCard->getValue(),
                    'string' => $drawnCard->getCardAsString()
                ];
            } else {
                break; // If there are no more cards left in the deck, stop drawing
            }
        }

        // Update the deck in the session
        $session->set('deck', $deck);

        // Prepare the response JSON structure
        $response = [
            'drawnCards' => $drawnCards,
            'remainingCards' => count($deck)
        ];

        // Return the JSON response
        return new JsonResponse($response);
    }

    #[Route("/api/deck/deal/{players}/{cards}", name: "api_deck_deal", methods: ["GET", "POST"])]
    public function dealCardsToPlayers(int $players, int $cards, SessionInterface $session): JsonResponse
    {
        // Retrieve the deck from the session
        $deck = $session->get('deck', []);

        // If the deck is not initialized or empty, return an error response
        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate the number of players and cards
        if ($players <= 0 || $cards <= 0) {
            return new JsonResponse(['error' => 'Invalid number of players or cards.'], Response::HTTP_BAD_REQUEST);
        }

        // Initialize an array to store each player's hand
        $playerHands = [];

        // Create a CardHand instance for each player
        for ($i = 1; $i <= $players; $i++) {
            $playerHands[] = new CardHand();
        }

        // Deal cards to each player
        for ($i = 0; $i < $cards; $i++) {
            foreach ($playerHands as $hand) {
                if (!empty($deck)) {
                    $drawnCard = array_shift($deck);
                    $hand->add($drawnCard);
                } else {
                    break; // If there are no more cards left in the deck, stop dealing
                }
            }
        }

        // Update the deck in the session
        $session->set('deck', $deck);

        // Prepare the response JSON structure
        $response = [
            'playerHands' => array_map(function ($hand) {
                return array_map(function ($card) {
                    return [
                        'value' => $card->getValue(),
                        'string' => $card->getCardAsString()
                    ];
                }, $hand->getHand());
            }, $playerHands),
            'remainingCards' => count($deck)
        ];

        // Return the JSON response
        return new JsonResponse($response);
    }
}
