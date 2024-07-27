<?php

namespace App\CardGame;

use App\CardGame\DeckOfCards;
use App\CardGame\CardHand;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GameService
{
    public function initializeGame(SessionInterface $session): void
    {
        $session->clear();
        $session->set('playerMoney', 100);
        $session->set('dealerMoney', 100);
    }

    public function initGame(SessionInterface $session, int $playerBet): bool
    {
        $deck = new DeckOfCards();
        $shuffledDeck = $deck->getDeck();
        shuffle($shuffledDeck);

        $playerHand = new CardHand();
        $dealerHand = new CardHand();

        if (!$playerHand->dealCards($shuffledDeck, 2) || !$dealerHand->dealCards($shuffledDeck, 2)) {
            return false;
        }

        $session->set('playerHand', $playerHand);
        $session->set('dealerHand', $dealerHand);
        $session->set('deck', $shuffledDeck);
        $session->set('playerBet', $playerBet);

        return true;
    }

    public function adjustMoneyForBet(SessionInterface $session): void
    {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        $session->set('playerMoney', $playerMoney - $playerBet);
        $session->set('dealerMoney', $dealerMoney - $playerBet);
    }

    public function isValidBet(SessionInterface $session): bool
    {
        $playerBet = $session->get('playerBet');
        $playerMoney = $session->get('playerMoney');
        $dealerMoney = $session->get('dealerMoney');

        return $playerBet >= 1 && $playerBet <= $playerMoney && $playerBet <= $dealerMoney;
    }
}
