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
        // Generate a new deck of cards
        $deck = DeckOfCards::generateDeck();
    
        // Store the new deck in the session
        $session->set('deck', $deck);
    
        // Render the deck template
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
        // Check if session data exists
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Please shuffle the deck.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        if (empty($deck)) {
            // Add flash message if the deck is empty
            $this->addFlash('warning', 'No more cards left in the deck. You have to reset the deck.');
        } else {
            // Draw a card from the deck
            $drawnCard = array_shift($deck);
            $session->set('deck', $deck);

            $cardHand = new CardHand();
            $cardHand->add($drawnCard);
        }

        // Render the draw template with the drawn card (or without if the deck is empty)
        return $this->render('card/draw.html.twig', [
            'drawnCards' => isset($cardHand) ? $cardHand->getString() : [],
            'remainingCards' => count($deck)
        ]);
    }
    
    //For when you select how many cards you want to draw:
    #[Route("/card/deck/draw", name: "card_draw_post", methods: ["POST"])]
    public function drawCardsPost(Request $request, SessionInterface $session): Response
    {
        // Retrieve the number of cards from the form submission
        $number = (int)$request->request->get('number');
    
        // Redirect to the route for drawing a specified number of cards
        return $this->redirectToRoute('card_draw_number', ['number' => $number]);
    }
    
    #[Route("/card/deck/draw/{number}", name: "card_draw_number", methods: ["GET", "POST"])]
    public function drawNumberCards(Request $request, SessionInterface $session, ?int $number = null): Response
    {
        // If the route parameter is not provided, try to get it from the request for POST requests
        if ($request->getMethod() === 'POST' && $number === null) {
            $number = (int)$request->request->get('number');
        }
    
        // Check if session data exists
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Please shuffle the deck.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        if (empty($deck)) {
            // Add flash message if the deck is empty
            $this->addFlash('warning', 'No more cards left in the deck. You have to reset the deck.');
        } else {
            $cardHand = new CardHand();

            // Draw the specified number of cards from the deck
            for ($i = 0; $i < $number; $i++) {
                if (!empty($deck)) {
                    $drawnCard = array_shift($deck);
                    $cardHand->add($drawnCard);
                } else {
                    break; // If there are no more cards left in the deck, stop drawing
                }
            }

            // Update the deck in the session
            $session->set('deck', $deck);
        }

        // Render the draw_number template with the drawn cards (or without if the deck is empty)
        return $this->render('card/draw_number.html.twig', [
            'drawnCards' => isset($cardHand) ? $cardHand->getString() : [],
            'remainingCards' => count($deck)
        ]);
    }
}