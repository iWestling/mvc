<?php

namespace App\Tests\CardGame;

use App\CardGame\CardGraphic;
use App\CardGame\NormalComputer;
use App\CardGame\Player;
use PHPUnit\Framework\TestCase;

class NormalComputerTest extends TestCase
{
    private NormalComputer $computer;

    protected function setUp(): void
    {
        $this->computer = new NormalComputer();
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

    public function testCallWhenPairExistsAndCurrentBetGreaterThanZero(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getHand')->willReturn([
            new CardGraphic(3, 'hearts'),
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
}
