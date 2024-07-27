<?php

namespace App\Controller;

use App\CardGame\CardGraphic;
use App\CardGame\CardHand;
use App\CardGame\GameResultCheck;
use App\CardGame\MoneyHandling;
use App\CardGame\GameService;
use App\CardGame\GameLogger;
use App\CardGame\GameDataService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlackJackController extends AbstractController
{
    private GameService $gameService;
    private GameLogger $gameLogger;
    private GameDataService $gameDataService;

    public function __construct(GameService $gameService, GameLogger $gameLogger, GameDataService $gameDataService)
    {
        $this->gameService = $gameService;
        $this->gameLogger = $gameLogger;
        $this->gameDataService = $gameDataService;
    }

    #[Route("/game", name: "game", methods: ['GET'])]
    public function game(SessionInterface $session): Response
    {
        $this->gameService->initializeGame($session);
        return $this->render('blackjack/home.html.twig');
    }

    #[Route("/game/init", name: "game_init", methods: ['GET'])]
    public function init(SessionInterface $session): Response
    {
        $data = [
            'playerMoney' => $session->get('playerMoney'),
            'dealerMoney' => $session->get('dealerMoney'),
        ];

        return $this->render('blackjack/init.html.twig', $data);
    }

    #[Route("/game/init", name: "game_init_post", methods: ['POST'])]
    public function initCallback(Request $request, SessionInterface $session): Response
    {
        $playerBet = (int)$request->request->get('playerbet');

        if (!$this->gameService->initGame($session, $playerBet)) {
            $this->addFlash('error', 'Failed to deal cards.');
            return $this->redirectToRoute('game_init');
        }

        return $this->redirectToRoute('game_play');
    }

    #[Route("/game/play", name: "game_play", methods: ['GET'])]
    public function play(SessionInterface $session): Response
    {
        if (!$this->gameService->isValidBet($session)) {
            $this->addFlash('error', 'Wrongful bet. Please try again or reset game.');
            return $this->redirectToRoute('game_init');
        }

        $this->gameService->adjustMoneyForBet($session);

        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');

        // Log the game start only if it hasn't been logged yet
        if (!$session->get('gameStarted')) {
            $this->gameLogger->logGameStart($session, $playerHand, $dealerHand);
            $session->set('gameStarted', true);
        }

        $playerTotals = $playerHand->calculateTotal();
        $dealerTotals = $dealerHand->calculateTotalDealer();

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = $dealerHandCards[1]->getUnturned();

        $data = $this->gameDataService->getGameData($session, $playerHand, $dealerHand, $playerTotals, $dealerTotals, $blackjackOrBust);
        $data['dealerUnturned'] = $dealerHandCards[1];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/stand", name: "game_player_stand", methods: ['GET', 'POST'])]
    public function stand(SessionInterface $session): Response
    {
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');

        // Update the dealer's unturned card to the real card image path
        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $dealerCard2Value = $dealerHandCards[1]->getCardName();
        $dealerCard2Suit = $dealerHandCards[1]->getSuit();

        $this->gameLogger->updateGameLog($session, "Player stands.\nTurned over dealer's second card: {$dealerCard2Value} of {$dealerCard2Suit}\n");

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $session->set('dealerHand', $dealerHand);

        $data = $this->gameDataService->getGameData($session, $playerHand, $dealerHand, $playerTotals, $dealerTotals, $blackjackOrBust);

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/hit", name: "game_player_hit", methods: ['GET', 'POST'])]
    public function hit(SessionInterface $session): Response
    {
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');

        $deck = $session->get('deck');
        if (!is_array($deck)) {
            $this->addFlash('error', 'Deck is not properly initialized.');
            return $this->redirectToRoute('game');
        }

        $drawnCard = array_shift($deck);
        if (!$drawnCard instanceof CardGraphic) {
            $this->addFlash('error', 'Failed to draw a card from the deck.');
            return $this->redirectToRoute('game');
        }
        $playerHand->addCard($drawnCard);
        $session->set('deck', $deck);
        $session->set('playerHand', $playerHand);

        $playerTotals = $playerHand->calculateTotal();
        $dealerTotals = $dealerHand->calculateTotalDealer();

        $this->gameLogger->updateGameLog($session, "Player drew another card: {$drawnCard->getCardName()} of {$drawnCard->getSuit()}\n");

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }

        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = $dealerHandCards[1]->getUnturned();

        $data = $this->gameDataService->getGameData($session, $playerHand, $dealerHand, $playerTotals, $dealerTotals, $blackjackOrBust);
        $data['dealerUnturned'] = $dealerHandCards[1];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/dealer-hit", name: "game_dealer_hit", methods: ['GET'])]
    public function dealerHit(SessionInterface $session): Response
    {
        /** @var CardHand $playerHand */
        $playerHand = $session->get('playerHand');
        /** @var CardHand $dealerHand */
        $dealerHand = $session->get('dealerHand');

        $deck = $session->get('deck');
        if (!is_array($deck)) {
            $this->addFlash('error', 'Deck is not properly initialized.');
            return $this->redirectToRoute('game');
        }

        // Get turned cards value
        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = new CardGraphic($dealerHandCards[1]->getValue(), $dealerHandCards[1]->getSuit());

        $drawnCard = array_shift($deck);
        if (!$drawnCard instanceof CardGraphic) {
            $this->addFlash('error', 'Failed to draw a card from the deck.');
            return $this->redirectToRoute('game');
        }
        $dealerHand->addCard($drawnCard);

        $this->gameLogger->updateGameLog($session, "Dealer drew another card: {$drawnCard->getCardName()} of {$drawnCard->getSuit()}\n");

        $session->set('deck', $deck);
        $session->set('dealerHand', $dealerHand);

        $dealerTotals = $dealerHand->calculateTotal();
        $playerTotals = $playerHand->calculateTotal();

        $gameResultCheck = new GameResultCheck();
        $blackjackOrBust = $gameResultCheck->blackjackOrBust($playerTotals, $dealerTotals);
        if (!empty($blackjackOrBust)) {
            return $this->redirectToRoute('game_end_result');
        }
        if ($dealerTotals['low'] > 16) {
            return $this->redirectToRoute('game_end_result');
        }

        $dealerHandCards = $dealerHand->getCards();
        $dealerHandCards[1] = $dealerHandCards[1]->getUnturned();

        $data = $this->gameDataService->getGameData($session, $playerHand, $dealerHand, $playerTotals, $dealerTotals, $blackjackOrBust);
        $data['dealerUnturned'] = $dealerHandCards[1];

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/end-result", name: "game_end_result", methods: ['GET'])]
    public function endResult(SessionInterface $session): Response
    {
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

        $this->gameLogger->updateGameLog($session, "\nRound ended.");

        $gameLogLines = explode("\n", $gameLog);
        $gameResult = end($gameLogLines) ?: '';

        $moneyHandler = new MoneyHandling();
        $playerBet = filter_var($session->get('playerBet'), FILTER_VALIDATE_INT);
        $playerMoney = filter_var($session->get('playerMoney'), FILTER_VALIDATE_INT);
        $dealerMoney = filter_var($session->get('dealerMoney'), FILTER_VALIDATE_INT);
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

        $data = $this->gameDataService->getGameData($session, $playerHand, $dealerHand, $playerTotals, $dealerTotals, $gameResult);

        // Reset the game state
        $session->remove('gameStarted');

        return $this->render('blackjack/play.html.twig', $data);
    }

    #[Route("/game/doc", name: "game_doc", methods: ['GET'])]
    public function doc(): Response
    {
        return $this->render('blackjack/doc.html.twig');
    }
}
