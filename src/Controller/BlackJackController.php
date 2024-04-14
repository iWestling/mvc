<?php

namespace App\Controller;

use App\CardGame\Card;
use App\CardGame\CardGraphic;
use App\CardGame\DeckOfCards;
use App\CardGame\CardHand;
use App\CardGame\GameCheck;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlackJackController extends AbstractController
{

    #[Route("/game", name: "game", methods: ['GET'])]
    public function game(): Response
    {
        return $this->render('blackjack/home.html.twig');
    }

    #[Route("/game/init", name: "game_init", methods: ['GET'])]
    public function init(): Response
    {        
        return $this->render('blackjack/init.html.twig');
    }

    #[Route("/game/init", name: "game_init_post", methods: ['POST'])]
    public function initCallback(
        Request $request,
        SessionInterface $session
    ): Response {
    
        $playerBet = $request->request->get('playerbet');
        // Initialize the bank
        $session->set('playerMoney', 100);
        $session->set('dealerMoney', 100);

        // New shuffled deck
        $deck = new DeckOfCards();
        $shuffledDeck = $deck->getDeck();
        shuffle($shuffledDeck);

        // Store the shuffled deck in the session
        $session->set('deck', $shuffledDeck);
        $session->set('playerBet', $playerBet);

        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/play", name: "game_play", methods: ['GET'])]
    public function play(
        Request $request,
        SessionInterface $session
    ): Response {

        // Retrieve player's bet from session
        $playerBet = $session->get('playerBet');
    
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        // Check if the player has enough money for the bet
        if ($playerBet < 1 || $playerBet > $playerMoney || $playerBet > $dealerMoney) {
            // flash message for bad bet
            $this->addFlash('error', 'Wrongful bet. Please try again or reset your bank.');

            // Redirect back to the init page
            return $this->redirectToRoute('game_init');
        }

        // Set the player's bet and dealer's bet in the session
        $session->set('playerBet', $playerBet);

        // Deal cards
        $deck = $session->get('deck');
        $playerHand = new CardHand();
        $dealerHand = new CardHand();

        // Deal 1 card face up to player
        $playerHand->addCard(array_shift($deck));

        // Deal 1 card face up to dealer
        $dealerHand->addCard(array_shift($deck));

        // Deal 1 card face up to player
        $playerHand->addCard(array_shift($deck));

        // Deal 1 card face down to dealer
        $dealerHand->addCard(array_shift($deck));

        // Store hands in session
        $session->set('playerHand', $playerHand);
        $session->set('dealerHand', $dealerHand);

        // Prepare player and dealer hands for display
        $playerHandData = [];
        foreach ($playerHand->getCards() as $card) {
            $playerHandData[] = $card->getAsString();
        }

        $dealerHandData = [];
        foreach ($dealerHand->getCards() as $card) {
            $dealerHandData[] = $card->getAsString();
        }

        // Prepare unturned card for dealer
        $dealerUnturned = $dealerHand->getCards()[0]->getUnturned();

        // Calculate total score for player and dealer
        $playerTotals = $playerHand->calculateTotal();
        $playerTotalLow = $playerTotals['low'];
        $playerTotalHigh = $playerTotals['high'];

        $dealerTotals = $dealerHand->calculateTotalDealer();
        $dealerTotalLow = $dealerTotals['low'];
        $dealerTotalHigh = $dealerTotals['high'];

        $resultMessage = '';

        if ($playerTotals['high'] > 21 && $playerTotals['low'] > 21) {
            // Player loses
            $resultMessage = 'You lose!';
        }
    
        if (($dealerTotals['high'] === 21 || $dealerTotals['low'] === 21)) {
            // Check if dealer's hand has a 21
            $dealerHasBlackjack = false;
            foreach ($dealerHand->getCards() as $card) {
                if ($card->getValue() === 1 || $card->getValue() >= 10) {
                    $dealerHasBlackjack = true;
                    break;
                }
            }
    
            if ($dealerHasBlackjack) {
                // It's a tie
                $resultMessage = 'It\'s a tie!';
            } else {
                // Player wins
                $resultMessage = 'You win!';
            }
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
            'resultMessage' => $resultMessage,
        ];        
        
        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/stand", name: "game_player_stand", methods: ['GET'])]
    public function stand(
        Request $request,
        SessionInterface $session
    ): Response {

        $data = [
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }
    
    #[Route("/game/hit", name: "game_player_hit", methods: ['GET'])]
    public function hit(
        Request $request,
        SessionInterface $session
    ): Response {

        $data = [
        ];

        return $this->render('blackjack/play.html.twig', $data);
    }

    // #[Route("/game/win", name: "game_player_win", methods: ['GET'])]
    // public function win(
    //     Request $request,
    //     SessionInterface $session
    // ): Response {
    //     // Retrieve player's bet from session
    //     $playerBet = $session->get('playerBet');
    
    //     // Retrieve player and dealer money from session
    //     $playerMoney = $session->get('playerMoney');
    //     $dealerMoney = $session->get('dealerMoney');
    
    //     // Add player's bet to player's money
    //     $playerMoney += $playerBet;
    
    //     // Remove player's bet from dealer's money
    //     $dealerMoney -= $playerBet;
    
    //     // Update player and dealer money in session
    //     $session->set('playerMoney', $playerMoney);
    //     $session->set('dealerMoney', $dealerMoney);
    
    //     $data = [
    //         'message' => 'You won!',
    //     ];
    
    //     return $this->render('blackjack/result.html.twig', $data);
    // }
    
    // #[Route("/game/tie", name: "game_player_tie", methods: ['GET'])]
    // public function tie(
    //     Request $request,
    //     SessionInterface $session
    // ): Response {
    //     $data = [
    //         'message' => 'It\'s a tie!',
    //     ];
    
    //     return $this->render('blackjack/result.html.twig', $data);
    // }
    
    // #[Route("/game/lose", name: "game_player_lose", methods: ['GET'])]
    // public function lose(
    //     Request $request,
    //     SessionInterface $session
    // ): Response {
    //     // Retrieve player's bet from session
    //     $playerBet = $session->get('playerBet');
    
    //     // Retrieve player and dealer money from session
    //     $playerMoney = $session->get('playerMoney');
    //     $dealerMoney = $session->get('dealerMoney');
    
    //     // Remove player's bet from player's money
    //     $playerMoney -= $playerBet;
    
    //     // Add player's bet to dealer's money
    //     $dealerMoney += $playerBet;
    
    //     // Update player and dealer money in session
    //     $session->set('playerMoney', $playerMoney);
    //     $session->set('dealerMoney', $dealerMoney);
    
    //     $data = [
    //         'message' => 'You lost!',
    //     ];
    
    //     return $this->render('blackjack/result.html.twig', $data);
    // }
    
}
