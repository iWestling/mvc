<?php

namespace App\Tests\CardGame;

use App\CardGame\CardGraphic;
use App\CardGame\IntelligentComputer;
use App\CardGame\Player;
use PHPUnit\Framework\TestCase;

class IntelligentComputerTest extends TestCase
{
    private IntelligentComputer $computer;

    protected function setUp(): void
    {
        $this->computer = new IntelligentComputer();
    }

    public function testFoldWhenNoPairOrHighCardAndCurrentBetGreaterThanZero(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getHand')->willReturn([
            new CardGraphic(3, 'hearts'),
            new CardGraphic(4, 'diamonds'),
        ]);

        $communityCards = [
            new CardGraphic(5, 'clubs'),
            new CardGraphic(8, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $decision = $this->computer->makeDecision($player, $communityCards, 10);
        $this->assertEquals('fold', $decision);
    }

    public function testCallWhenHighCardExistsAndCurrentBetGreaterThanZero(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getHand')->willReturn([
            new CardGraphic(12, 'hearts'), // Queen (high card)
            new CardGraphic(3, 'diamonds'),
        ]);

        $communityCards = [
            new CardGraphic(5, 'clubs'),
            new CardGraphic(8, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $decision = $this->computer->makeDecision($player, $communityCards, 10);
        $this->assertEquals('call', $decision);
    }

    public function testCheckWhenNoCurrentBet(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getHand')->willReturn([
            new CardGraphic(3, 'hearts'),
            new CardGraphic(4, 'diamonds'),
        ]);

        $communityCards = [
            new CardGraphic(5, 'clubs'),
            new CardGraphic(8, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $decision = $this->computer->makeDecision($player, $communityCards, 0);
        $this->assertEquals('check', $decision);
    }

    public function testRaiseWithThreeOfAKind(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getHand')->willReturn([
            new CardGraphic(3, 'hearts'),
            new CardGraphic(3, 'diamonds'),
        ]);

        $communityCards = [
            new CardGraphic(3, 'spades'), // Three of a kind setup
            new CardGraphic(8, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $decision = $this->computer->makeDecision($player, $communityCards, 0);
        $this->assertEquals('raise', $decision);
    }
}
