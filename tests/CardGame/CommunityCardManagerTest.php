<?php

namespace App\Tests\CardGame;

use App\CardGame\CommunityCardManager;
use App\CardGame\Deck;
use App\CardGame\CardGraphic;
use App\CardGame\Player;
use PHPUnit\Framework\TestCase;

class CommunityCardManagerTest extends TestCase
{
    public function testDealCommunityCardsAddsCardsToCommunity(): void
    {
        $deck = $this->createMock(Deck::class);
        $deck->method('drawCard')->willReturn(new CardGraphic(1, 'hearts'));

        $communityCardManager = new CommunityCardManager($deck);
        $communityCardManager->dealCommunityCards(3);

        $this->assertCount(3, $communityCardManager->getCommunityCards());
        foreach ($communityCardManager->getCommunityCards() as $card) {
            $this->assertInstanceOf(CardGraphic::class, $card);
            $this->assertEquals('hearts', $card->getSuit());
            $this->assertEquals(1, $card->getValue());
        }
    }

    public function testGetCommunityCardsReturnsEmptyArrayWhenNoCardsDealt(): void
    {
        $deck = $this->createMock(Deck::class);
        $communityCardManager = new CommunityCardManager($deck);

        $this->assertEmpty($communityCardManager->getCommunityCards());
    }

    public function testResetCommunityCardsClearsCommunityCards(): void
    {
        $deck = $this->createMock(Deck::class);
        $deck->method('drawCard')->willReturn(new CardGraphic(1, 'hearts'));

        $communityCardManager = new CommunityCardManager($deck);
        $communityCardManager->dealCommunityCards(3);

        $this->assertCount(3, $communityCardManager->getCommunityCards());

        $communityCardManager->resetCommunityCards();
        $this->assertEmpty($communityCardManager->getCommunityCards());
    }

    public function testDealInitialCardsAddsCardsToPlayerHands(): void
    {
        $deck = $this->createMock(Deck::class);
        $deck->method('drawCard')->willReturn(new CardGraphic(1, 'hearts'));

        // Creating mock players
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);

        $player1->expects($this->exactly(2))->method('addCardToHand');
        $player2->expects($this->exactly(2))->method('addCardToHand');

        $communityCardManager = new CommunityCardManager($deck);
        $communityCardManager->dealInitialCards([$player1, $player2]);

    }
}
