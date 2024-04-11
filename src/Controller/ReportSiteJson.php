<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Card\DeckOfCards;
use App\Card\CardHand;
use App\Card\CardGraphic;

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
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();
        $session->set('deck', $deck);

        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'card' => $card->getForAPI(),
                'imagepath' => $card->getAsString()
            ];
        }, $deck);

        // JSON pretty print
        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api_deck_shuffle", methods: ["GET", "POST"])]
    public function shuffleDeck(SessionInterface $session): JsonResponse
    {
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        // Shuffle
        shuffle($deck);

        $session->set('deck', $deck);

        // JSON
        $deckArray = array_map(function ($card) {
            return [
                'value' => $card->getValue(),
                'card' => $card->getForAPI(),
                'imagepath' => $card->getAsString()
            ];
        }, $deck);

        $response = new JsonResponse($deckArray);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/draw", name: "api_deck_draw", methods: ["GET", "POST"])]
    public function drawCardFromDeck(SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', []);

        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        // Draw 1 card from deck
        $drawnCard = array_shift($deck);

        $session->set('deck', $deck);

        // JSON structure
        $cardGraphic = new CardGraphic($drawnCard->getValue());
        $response = [
            'drawnCard' => [
                'value' => $drawnCard->getValue(),
                'card' => $drawnCard->getForAPI(),
                'imagepath' => $cardGraphic->getAsString()
            ],
            'remainingCards' => count($deck)
        ];

        $response = new JsonResponse($response);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/draw/{number}", name: "api_deck_draw_multiple", methods: ["GET", "POST"])]
    public function drawMultipleCardsFromDeck(Request $request, SessionInterface $session, int $number): JsonResponse
    {
        $deck = $session->get('deck', []);

        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        if ($number <= 0) {
            return new JsonResponse(['error' => 'Invalid number of cards to draw.'], Response::HTTP_BAD_REQUEST);
        }

        // Draw x cards
        $drawnCards = [];
        for ($i = 0; $i < $number; $i++) {
            if (!empty($deck)) {
                $drawnCard = array_shift($deck);
                $cardGraphic = new CardGraphic($drawnCard->getValue());
                $drawnCards[] = [
                    'value' => $drawnCard->getValue(),
                    'card' => $drawnCard->getForAPI(),
                    'imagepath' => $cardGraphic->getAsString()
                ];
            } else {
                break;
            }
        }

        // Update deck in session
        $session->set('deck', $deck);

        $response = [
            'drawnCards' => $drawnCards,
            'remainingCards' => count($deck)
        ];

        $response = new JsonResponse($response);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route("/api/deck/deal/{players}/{cards}", name: "api_deck_deal", methods: ["GET", "POST"])]
    public function dealCardsToPlayers(int $players, int $cards, SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', []);

        if (empty($deck)) {
            return new JsonResponse(['error' => 'No cards in the deck. Please shuffle the deck.'], Response::HTTP_BAD_REQUEST);
        }

        if ($players <= 0 || $cards <= 0) {
            return new JsonResponse(['error' => 'Invalid number of players or cards.'], Response::HTTP_BAD_REQUEST);
        }

        $cardHand = new CardHand();
        $playerHands = $cardHand->dealCardsToPlayers($deck, $players, $cards);

        $session->set('deck', $deck);

        $playerHandsData = [];
        foreach ($playerHands as $index => $hand) {
            $playerName = 'Player ' . ($index + 1);
            $playerHandsData[$playerName] = array_map(function ($card) {
                $cardGraphic = new CardGraphic($card->getValue());
                return [
                    'value' => $card->getValue(),
                    'card' => $card->getForAPI(),
                    'imagepath' => $cardGraphic->getAsString()
                ];
            }, $hand->getHand());
        }

        $response = [
            'playerHands' => $playerHandsData,
            'remainingCards' => count($deck)
        ];

        $response = new JsonResponse($response);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
