<?php

namespace App\Controller;

use App\CardGame\Card;
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

        $session->set('playerMoney', 100);
        $session->set('dealerMoney', 100);
        $gameLog = "Started new game.\nMoney set to 100 for player and dealer\n";
        $session->set('gameLog', $gameLog);
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
    
        $playerBet = $request->request->get('playerbet');
        $deck = new DeckOfCards();
        $shuffledDeck = $deck->getDeck();
        shuffle($shuffledDeck);

        $playerHand = new CardHand();
        $dealerHand = new CardHand();

        $playerCard1 = array_shift($shuffledDeck);
        $playerCard2 = array_shift($shuffledDeck);
        $playerHand->addCard($playerCard1);
        $playerHand->addCard($playerCard2);

        $dealerCard1 = array_shift($shuffledDeck);
        $dealerCard2 = array_shift($shuffledDeck);
        $dealerHand->addCard($dealerCard1);
        $dealerHand->addCard($dealerCard2);

        $session->set('playerHand', $playerHand);
        $session->set('dealerHand', $dealerHand);

        $session->set('deck', $shuffledDeck);
        $session->set('playerBet', $playerBet);

        $playerCard1Value = $playerCard1->getCardName();
        $playerCard1Suit = $playerCard1->getSuit();
        $playerCard2Value = $playerCard2->getCardName();
        $playerCard2Suit = $playerCard2->getSuit();
        $dealerCard1Value = $dealerCard1->getCardName();
        $dealerCard1Suit = $dealerCard1->getSuit();
        $gameLog = $session->get('gameLog');
        $gameLog .= "\nStarted new round\nRegistered Bet as {$playerBet}\n";
        $gameLog .= "Dealt cards to player: {$playerCard1Value} of {$playerCard1Suit}, {$playerCard2Value} of {$playerCard2Suit}\n";
        $gameLog .= "Dealt cards to dealer: {$dealerCard1Value} of {$dealerCard1Suit}, [Hidden Card]\n";
        $session->set('gameLog', $gameLog);

        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/play", name: "game_play", methods: ['GET'])]
    public function play(
        Request $request,
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

        $playerMoney = $playerMoney-$playerBet;
        $dealerMoney = $dealerMoney-$playerBet;
        $session->set('playerMoney', $playerMoney);
        $session->set('dealerMoney', $dealerMoney);

        $playerHand = $session->get('playerHand');
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

        $blackjackOrBust = GameResultCheck::blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)){
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn($card) => $card->getAsString(), $dealerHand->getCards()),
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
        Request $request,
        SessionInterface $session
    ): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        $playerHand = $session->get('playerHand');
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

        $blackjackOrBust = GameResultCheck::blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)){
            return $this->redirectToRoute('game_end_result');
        }

        $session->set('dealerHand', $dealerHand);
    
        $data = [
            'playerHand' => array_map(fn($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn($card) => $card->getAsString(), $dealerHand->getCards()),
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
        Request $request,
        SessionInterface $session
    ): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        $playerHand = $session->get('playerHand');
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');

        $deck = $session->get('deck');
        $drawnCard = array_shift($deck);
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

        $blackjackOrBust = GameResultCheck::blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)){
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn($card) => $card->getAsString(), $dealerHand->getCards()),
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
    public function dealerHit(Request $request, SessionInterface $session): Response {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        $playerHand = $session->get('playerHand');
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');
        $deck = $session->get('deck');

        //get turned cards value
        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        $drawnCard = array_shift($deck);
        $dealerHand->addCard($drawnCard);
        $drawnCardValue = $drawnCard->getCardName();
        $drawnCardSuit = $drawnCard->getSuit();
        $gameLog .= "Dealer drew another card: {$drawnCardValue} of {$drawnCardSuit}\n";
    

        $session->set('deck', $deck);
        $session->set('dealerHand', $dealerHand);
        $session->set('gameLog', $gameLog);

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $blackjackOrBust = GameResultCheck::blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)){
            return $this->redirectToRoute('game_end_result');
        }

        if (empty($blackjackOrBust) && $dealerTotals['low'] > 16) {
            return $this->redirectToRoute('game_end_result');
        }

        $data = [
            'playerHand' => array_map(fn($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn($card) => $card->getAsString(), $dealerHand->getCards()),
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
    public function endResult(Request $request, SessionInterface $session): Response {

        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');
        $playerHand = $session->get('playerHand');
        $dealerHand = $session->get('dealerHand');
        $gameLog = $session->get('gameLog');
    
        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        // Check the final outcome of the game
        $blackjackOrBust = GameResultCheck::blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)){
            $gameLog .= $blackjackOrBust;
        }
        if (empty($blackjackOrBust)){
            $highestScore = GameResultCheck::highestScore($playerTotals, $dealerTotals);
            $gameLog .= $highestScore;
        }
        $session->set('gameLog', $gameLog);

        $gameLogLines = explode("\n", $gameLog);
        $gameResult = end($gameLogLines); // Get the last line of the gameLog
        list($playerMoney, $dealerMoney) = MoneyHandling::handleMoney($gameResult, $playerBet, $playerMoney, $dealerMoney);
        $session->set('playerMoney', $playerMoney);
        $session->set('dealerMoney', $dealerMoney);

        $data = [
            'playerHand' => array_map(fn($card) => $card->getAsString(), $playerHand->getCards()),
            'dealerHand' => array_map(fn($card) => $card->getAsString(), $dealerHand->getCards()),
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
