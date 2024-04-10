<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
    #[Route("/session", name: "session_show")]
    public function showSession(SessionInterface $session): Response
    {
        $sessionData = $session->all();
        return $this->render('card/session.html.twig', ['sessionData' => $sessionData]);
    }

    #[Route("/session/delete", name: "session_delete")]
    public function deleteSession(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('notice', 'Session data has been deleted.');
        return $this->redirectToRoute('card_start');
    }

    #[Route("/card", name: "card_start")]
    public function home(): Response
    {
        return $this->render('card/home.html.twig');
    }

    #[Route("/card/deck", name: "card_deck", methods: ["GET"])]
    public function showDeck(SessionInterface $session): Response
    {
        // New deck
        $deck = DeckOfCards::generateDeck();

        // Save deck in session
        $session->set('deck', $deck);

        return $this->render('card/deck.html.twig', ['deck' => $deck]);
    }


    #[Route("/card/deck/shuffle", name: "card_shuffle", methods: ["GET", "POST"])]
    public function shuffleDeck(SessionInterface $session): Response
    {
        $deck = DeckOfCards::generateDeck();

        shuffle($deck);

        $session->set('deck', $deck);

        return $this->render('card/shuffle.html.twig', ['deck' => $deck]);
    }

    //For when you only want to draw 1 card:
    #[Route("/card/deck/draw", name: "card_draw")]
    public function drawCard(SessionInterface $session): Response
    {
        // Check
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        } else {
            // Draw card
            $drawnCard = array_shift($deck);
            $session->set('deck', $deck);

            $cardHand = new CardHand();
            $cardHand->add($drawnCard);
        }

        return $this->render('card/draw.html.twig', [
            'drawnCards' => isset($cardHand) ? $cardHand->getHand() : [],
            'remainingCards' => count($deck)
        ]);
    }

    //For when you select how many cards you want to draw:
    #[Route("/card/deck/draw", name: "card_draw_post", methods: ["POST"])]
    public function drawCardsPost(Request $request, SessionInterface $session): Response
    {
        // get number of cards from form
        $number = (int)$request->request->get('number');

        return $this->redirectToRoute('card_draw_number', ['number' => $number]);
    }

    #[Route("/card/deck/draw/{number}", name: "card_draw_number", methods: ["GET", "POST"])]
    public function drawNumberCards(Request $request, SessionInterface $session, ?int $number = null): Response
    {
        if ($request->getMethod() === 'POST' && $number === null) {
            $number = (int)$request->request->get('number');
        }

        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Resetting deck.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        } else {
            $cardHand = new CardHand();

            // Draw X number of cards
            for ($i = 0; $i < $number; $i++) {
                if (!empty($deck)) {
                    $drawnCard = array_shift($deck);
                    $cardHand->add($drawnCard); // Add card
                } else {
                    break;
                }
            }

            // Update deck
            $session->set('deck', $deck);
        }

        return $this->render('card/draw_number.html.twig', [
            'drawnCards' => isset($cardHand) ? $cardHand->getHand() : [],
            'remainingCards' => count($deck)
        ]);
    }

    #[Route("/card/deck/deal/{players}/{cards}", name: "card_deal")]
    public function dealCards(int $players, int $cards, SessionInterface $session): Response
    {
        // Check
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        $playerHands = [];

        // CardHand instance for each player
        for ($i = 1; $i <= $players; $i++) {
            $playerHands[] = new CardHand();
        }

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        } else {
            // Deal to each player
            for ($i = 0; $i < $cards; $i++) {
                foreach ($playerHands as $hand) {
                    if (!empty($deck)) {
                        $drawnCard = array_shift($deck);
                        $hand->add($drawnCard);
                    } else {
                        break;
                    }
                }
            }
        }

        // Update deck
        $session->set('deck', $deck);

        return $this->render('card/deal.html.twig', [
            'playerHands' => $playerHands,
            'remainingCards' => count($deck)
        ]);
    }

}
