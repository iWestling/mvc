<?php

namespace App\Controller;

use App\CardGame\CardGraphic;
use App\CardGame\DeckOfCards;
use App\CardGame\CardHand;
use App\CardGame\GameResultCheck;
use App\CardGame\MoneyHandling;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlackJackController extends AbstractController
{
    #[Route("/game", name: "game", methods: ['GET'])]
    public function game(
        SessionInterface $session
    ): Response {

        $session->set('playerMoney', (int) 100);
        $session->set('dealerMoney', (int) 100);
        return $this->render('blackjack/home.html.twig');
    }

    #[Route("/game/init", name: "game_init", methods: ['GET'])]
    public function init(
        SessionInterface $session
    ): Response {
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        $data = [
            'playerMoney' => $playerMoney,
            'dealerMoney' => $dealerMoney,
        ];

        return $this->render('blackjack/init.html.twig', $data);
    }

    #[Route("/game/init", name: "game_init_post", methods: ['POST'])]
    public function initCallback(
        Request $request,
        SessionInterface $session
    ): Response {

        $playerBet = (int) $request->request->get('playerbet');
        $deck = new DeckOfCards();
        $shuffledDeck = $deck->getDeck();
        shuffle($shuffledDeck);

        $playerHand = new CardHand();
        $dealerHand = new CardHand();

        // Deal cards to player
        $playerDealSuccess = true;
        for ($i = 0; $i < 2; $i++) {
            $card = array_shift($shuffledDeck);
            if (!($card instanceof CardGraphic)) {
                $playerDealSuccess = false;
                break;
            }
            $playerHand->addCard($card);
        }
        if (!$playerDealSuccess) {
            $this->addFlash('error', 'Failed to deal card to player.');
            return $this->redirectToRoute('game_init');
        }

        // Deal cards to dealer
        $dealerDealSuccess = true;
        for ($i = 0; $i < 2; $i++) {
            $card = array_shift($shuffledDeck);
            if (!($card instanceof CardGraphic)) {
                $dealerDealSuccess = false;
                break;
            }
            $dealerHand->addCard($card);
        }

        if (!$dealerDealSuccess) {
            $this->addFlash('error', 'Failed to deal card to dealer.');
            return $this->redirectToRoute('game_init');
        }

        $session->set('playerHand', $playerHand);
        $session->set('dealerHand', $dealerHand);
        $session->set('deck', $shuffledDeck);
        $session->set('playerBet', $playerBet);

        // Log game details
        $gameLog = $session->get('gameLog');
        $playerCards = array_map(fn ($card) => "{$card->getCardName()} of {$card->getSuit()}", $playerHand->getCards());
        $dealerCards = array_map(function ($card, $index) {
            if ($index === 1) {
                return '[Hidden Card]';
            }
            return "{$card->getCardName()} of {$card->getSuit()}";
        }, $dealerHand->getCards(), array_keys($dealerHand->getCards()));
        $gameLog .= "\n\nStarted new round\nRegistered Bet as {$playerBet}\n";
        $gameLog .= "Dealt cards to player: " . implode(', ', $playerCards) . "\n";
        $gameLog .= "Dealt cards to dealer: " . implode(', ', $dealerCards) . "\n";
        $session->set('gameLog', $gameLog);

        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/play", name: "game_play", methods: ['GET'])]
    public function play(
        SessionInterface $session
    ): Response {

        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        // Check if the player has enough money for the bet
        if ($playerBet < 1 || $playerBet > $playerMoney || $playerBet > $dealerMoney) {
            // flash message for bad bet
            $this->addFlash('error', 'Wrongful bet. Please try again or reset game.');
            return $this->redirectToRoute('game_init');
        }

        $playerMoney = $playerMoney - $playerBet;
        $dealerMoney = $dealerMoney - $playerBet;
        $session->set('playerMoney', $playerMoney);
        $session->set('dealerMoney', $dealerMoney);

        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');

        // Prepare unturned card for dealer
        $dealerUnturned = $dealerHand->getCards()[0]->getUnturned();

        // Calculate total score for player and dealer
        $playerTotals = $playerHand->calculateTotal();
        $playerTotalLow = $playerTotals['low'];
        $playerTotalHigh = $playerTotals['high'];

        $dealerTotals = $dealerHand->calculateTotalDealer();
        $dealerTotalLow = $dealerTotals['low'];
        $dealerTotalHigh = $dealerTotals['high'];

        $gameLog = $session->get('gameLog');

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerUnturned,
            'playerTotalLow' => $playerTotalLow,
            'playerTotalHigh' => $playerTotalHigh,
            'dealerTotalLow' => $dealerTotalLow,
            'dealerTotalHigh' => $dealerTotalHigh,
            'resultMessage' => $blackjackOrBust,
            'gameLog' => $gameLog,
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/stand", name: "game_player_stand", methods: ['GET', 'POST'])]
    public function stand(
        SessionInterface $session
    ): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');

        // Update the dealer's unturned card to the real card image path
        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $dealerCard2Value = $dealerHandCards[1]->getCardName();
        $dealerCard2Suit = $dealerHandCards[1]->getSuit();

        $gameLog .= "Player stands.\nTurned over dealers second card: {$dealerCard2Value} of {$dealerCard2Suit}\n";

        $session->set('gameLog', $gameLog);

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $session->set('dealerHand', $dealerHand);

        $data = [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerHandCards[1]->getAsString(),
            'playerTotalLow' => $playerHand->calculateTotal()['low'],
            'playerTotalHigh' => $playerHand->calculateTotal()['high'],
            'dealerTotalLow' => $dealerTotals['low'],
            'dealerTotalHigh' => $dealerTotals['high'],
            'resultMessage' => $blackjackOrBust,
            'gameLog' => $gameLog,
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }


    #[Route("/game/hit", name: "game_player_hit", methods: ['GET', 'POST'])]
    public function hit(
        SessionInterface $session
    ): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        if (!$playerHand instanceof CardHand) {
            // Handle the case where playerHand is not a CardHand instance
            $this->addFlash('error', 'Player hand not found or invalid.');
            return $this->redirectToRoute('game'); // Redirect to game initialization
        }
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');
        if (!$dealerHand instanceof CardHand) {
            // Handle the case where dealerHand is not a CardHand instance
            $this->addFlash('error', 'Dealer hand not found or invalid.');
            return $this->redirectToRoute('game'); // Redirect to game initialization
        }

        $gameLog = $session->get('gameLog');

        $deck = $session->get('deck');
        if (!is_array($deck)) {
            $this->addFlash('error', 'Deck is not properly initialized.');
            return $this->redirectToRoute('game');
        }
        $drawnCard = array_shift($deck);
        if (!$drawnCard instanceof CardGraphic) {
            // Handle the case where a non-CardGraphic object is drawn from the deck
            $this->addFlash('error', 'Failed to draw a card from the deck.');
            return $this->redirectToRoute('game'); // Redirect to game initialization
        }
        $playerHand->addCard($drawnCard);
        $session->set('deck', $deck);
        $session->set('playerHand', $playerHand);

        $dealerUnturned = $dealerHand->getCards()[0]->getUnturned();

        $playerTotals = $playerHand->calculateTotal();
        $dealerTotals = $dealerHand->calculateTotalDealer();

        $drawnCardValue = $drawnCard->getCardName();
        $drawnCardSuit = $drawnCard->getSuit();
        $gameLog .= "Player drew another card: {$drawnCardValue} of {$drawnCardSuit}\n";
        $session->set('gameLog', $gameLog);

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerUnturned,
            'playerTotalLow' => $playerHand->calculateTotal()['low'],
            'playerTotalHigh' => $playerHand->calculateTotal()['high'],
            'dealerTotalLow' => $dealerTotals['low'],
            'dealerTotalHigh' => $dealerTotals['high'],
            'resultMessage' => $blackjackOrBust,
            'gameLog' => $gameLog,
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/dealer-hit", name: "game_dealer_hit", methods: ['GET'])]
    public function dealerHit(SessionInterface $session): Response
    {
        /** @var int $playerBet */
        $playerBet = $session->get('playerBet');
        /** @var int $playerMoney */
        $playerMoney = $session->get('playerMoney');
        /** @var int $dealerMoney */
        $dealerMoney = $session->get('dealerMoney');
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');

        $deck = $session->get('deck');
        if (!is_array($deck)) {
            $this->addFlash('error', 'Deck is not properly initialized.');
            return $this->redirectToRoute('game');
        }
        //get turned cards value
        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        $drawnCard = array_shift($deck);
        if (!$drawnCard instanceof CardGraphic) {
            $this->addFlash('error', 'Failed to draw a card from the deck.');
            return $this->redirectToRoute('game');
        }
        $dealerHand->addCard($drawnCard);
        $drawnCardValue = $drawnCard->getCardName();
        $drawnCardSuit = $drawnCard->getSuit();
        $gameLog .= "Dealer drew another card: {$drawnCardValue} of {$drawnCardSuit}\n";


        $session->set('deck', $deck);
        $session->set('dealerHand', $dealerHand);
        $session->set('gameLog', $gameLog);

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust) || $dealerTotals['low'] > 16) {
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerHandCards[1]->getAsString(),
            'playerTotalLow' => $playerHand->calculateTotal()['low'],
            'playerTotalHigh' => $playerHand->calculateTotal()['high'],
            'dealerTotalLow' => $dealerTotals['low'],
            'dealerTotalHigh' => $dealerTotals['high'],
            'resultMessage' => $blackjackOrBust,
            'gameLog' => $gameLog,
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/end-result", name: "game_end_result", methods: ['GET'])]
    public function endResult(SessionInterface $session): Response
    {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');

        if (!is_string($gameLog)) {
            $gameLog = '';
        }

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        // Check the final outcome of the game
        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            $gameLog .= $blackjackOrBust;
        }
        if (empty($blackjackOrBust)) {
            $highestScore = $gameResultCheck->highestScore($playerTotals, $dealerTotals);
            $gameLog .= $highestScore;
        }
        $session->set('gameLog', $gameLog);

        $gameLogLines = explode("\n", $gameLog);
        $gameResult = end($gameLogLines) ?: '';

        $moneyHandler = new MoneyHandling();
        $playerBet = filter_var($playerBet, FILTER_VALIDATE_INT);
        $playerMoney = filter_var($playerMoney, FILTER_VALIDATE_INT);
        $dealerMoney = filter_var($dealerMoney, FILTER_VALIDATE_INT);
        // Check if the values are valid integers
        if ($playerBet !== false && $playerMoney !== false && $dealerMoney !== false) {
            list($playerMoney, $dealerMoney) = $moneyHandler->handleMoney(
                (string)$gameResult,
                $playerBet,
                $playerMoney,
                $dealerMoney
            );
        }
        $session->set('playerMoney', $playerMoney);
        $session->set('dealerMoney', $dealerMoney);

        $data = [
            'playerHand' => array_map(fn ($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn ($card) => $card->getAsString(), $dealerHand->getCards()),
            'playerMoney' => $playerMoney,
            'playerBet' => $playerBet,
            'dealerMoney' => $dealerMoney,
            'dealerUnturned' => $dealerHandCards[1]->getAsString(),
            'playerTotalLow' => $playerHand->calculateTotal()['low'],
            'playerTotalHigh' => $playerHand->calculateTotal()['high'],
            'dealerTotalLow' => $dealerTotals['low'],
            'dealerTotalHigh' => $dealerTotals['high'],
            'resultMessage' => $blackjackOrBust,
            'gameLog' => $gameLog,
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }
}
