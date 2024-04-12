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
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        $cardPaths = array_map(function (Card $card) {
            $cardGraphic = new CardGraphic($card->getValue());
            return $cardGraphic->getAsString();
        }, $deck);

        $session->set('deck', $deck);

        $data = [
            'deck' => $deck,
            'cardPaths' => $cardPaths,
            'remainingCards' => count($deck),
        ];

        return $this->render('card/deck.html.twig', $data);
    }

    #[Route("/card/deck/shuffle", name: "card_shuffle", methods: ["GET"])]
    public function shuffleDeck(SessionInterface $session): Response
    {
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        shuffle($deck);

        $cardPaths = array_map(function (Card $card) {
            $cardGraphic = new CardGraphic($card->getValue());
            return $cardGraphic->getAsString();
        }, $deck);

        $session->set('deck', $deck);

        $data = [
            'deck' => $deck,
            'cardPaths' => $cardPaths,
            'remainingCards' => count($deck),
        ];

        return $this->render('card/shuffle.html.twig', $data);
    }

    #[Route("/card/deck/draw", name: "card_draw")]
    public function drawCard(SessionInterface $session): Response
    {
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        /** @var Card[] $deck */
        $deck = $session->get('deck', []);

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        /** @var Card $drawnCard */
        $drawnCard = array_shift($deck);
        $session->set('deck', $deck);

        $cardGraphic = new CardGraphic($drawnCard->getValue());
        $drawnCardPath = $cardGraphic->getAsString();

        $data = [
            'drawnCardPaths' => [$drawnCardPath],
            'remainingCards' => count($deck),
        ];

        return $this->render('card/draw.html.twig', $data);
    }

    #[Route("/card/deck/draw", name: "card_draw_post", methods: ["POST"])]
    public function drawCardsPost(Request $request): Response
    {
        $number = (int) $request->request->get('number');

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

        /** @var Card[] $deck */
        $deck = $session->get('deck', []);

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        $cardHand = new CardHand();
        /** @var Card[] $drawnCards */
        $drawnCards = $cardHand->drawMultipleCards($deck, (int)$number);

        $session->set('deck', $deck);

        //graphic
        $drawnCardPaths = [];
        foreach ($drawnCards as $card) {
            $cardGraphic = new CardGraphic($card->getValue());
            $drawnCardPaths[] = $cardGraphic->getAsString();
        }

        $data = [
            'drawnCards' => $drawnCardPaths,
            'remainingCards' => count($deck),
        ];

        return $this->render('card/draw_number.html.twig', $data);
    }


    #[Route("/card/deck/deal", name: "card_deal_post", methods: ["POST"])]
    public function dealCardsPost(Request $request): Response
    {
        $players = (int)$request->request->get('players');
        $cards = (int)$request->request->get('cards');

        return $this->redirectToRoute('card_deal', ['players' => $players, 'cards' => $cards]);
    }

    #[Route("/card/deck/deal/{players}/{cards}", name: "card_deal", methods: ["GET", "POST"])]
    public function dealCardsGet(SessionInterface $session, int $players = 1, int $cards = 1): Response
    {
        if (!$session->has('deck')) {
            $this->addFlash('warning', 'No cards in deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        $deck = $session->get('deck', []);

        if (empty($deck)) {
            $this->addFlash('warning', 'No more cards left in the deck. Resetting deck, please try again.');
            return $this->redirectToRoute('card_deck');
        }

        $cardHand = new CardHand();
        $playerHands = $cardHand->dealCardsToPlayers($deck, $players, $cards);

        $session->set('deck', $deck);

        $playerCardPaths = [];

        //graphic
        foreach ($playerHands as $hand) {
            $playerCards = [];
            foreach ($hand->getHand() as $card) {
                $cardGraphic = new CardGraphic($card->getValue());
                $playerCardPath = $cardGraphic->getAsString();
                $playerCards[] = $playerCardPath;
            }
            $playerCardPaths[] = $playerCards;
        }

        $data = [
            'playerCardPaths' => $playerCardPaths,
            'remainingCards' => count($deck),
        ];

        return $this->render('card/deal.html.twig', $data);
    }
}
