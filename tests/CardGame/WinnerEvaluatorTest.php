<?php

namespace App\Tests\CardGame;

use App\CardGame\WinnerEvaluator;
use App\CardGame\HandEvaluator;
use App\CardGame\Player;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class WinnerEvaluatorTest extends TestCase
{
    private WinnerEvaluator $winnerEvaluator;

    /**
     * @var MockObject&HandEvaluator
     */
    private MockObject $handEvaluator;

    protected function setUp(): void
    {
        $this->handEvaluator = $this->createMock(HandEvaluator::class);
        $this->winnerEvaluator = new WinnerEvaluator($this->handEvaluator);
    }

    // public function testDetermineSingleWinner(): void
    // {
    //     $player1 = $this->createMock(Player::class);
    //     $player2 = $this->createMock(Player::class);
    //     $communityCards = [$this->createMock(CardGraphic::class)];

    //     // Mock player hands
    //     $player1->method('getHand')->willReturn([$this->createMock(CardGraphic::class)]);
    //     $player2->method('getHand')->willReturn([$this->createMock(CardGraphic::class)]);

    //     // Mock hand evaluator responses
    //     $this->handEvaluator->method('getBestHand')->willReturnOnConsecutiveCalls(
    //         ['rank' => 'pair', 'values' => [10]],  // Player 1's hand
    //         ['rank' => 'high card', 'values' => [7]]  // Player 2's hand
    //     );

    //     $this->handEvaluator->method('compareHands')->willReturnOnConsecutiveCalls(1, -1);

    //     // Test determining the winner
    //     $winners = $this->winnerEvaluator->determineWinners([$player1, $player2], $communityCards);

    //     // Assert that Player 1 is the winner
    //     $this->assertCount(1, $winners);
    //     $this->assertSame($player1, $winners[0]);
    // }


    public function testDetermineTie(): void
    {
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);
        $communityCards = [$this->createMock(CardGraphic::class)];

        $player1->method('getHand')->willReturn([$this->createMock(CardGraphic::class)]);
        $player2->method('getHand')->willReturn([$this->createMock(CardGraphic::class)]);

        $this->handEvaluator->method('getBestHand')->willReturnOnConsecutiveCalls(
            ['rank' => 'pair', 'values' => [10]],  // Player 1's hand
            ['rank' => 'pair', 'values' => [10]]   // Player 2's hand
        );

        $this->handEvaluator->method('compareHands')->willReturn(0);  // Both hands are equal

        // Test determining the winner
        $winners = $this->winnerEvaluator->determineWinners([$player1, $player2], $communityCards);

        $this->assertCount(2, $winners);
        $this->assertSame($player1, $winners[0]);
        $this->assertSame($player2, $winners[1]);
    }

    public function testDetermineWinnerWithEmptyPlayers(): void
    {
        $communityCards = [$this->createMock(CardGraphic::class)];

        // Test with no players
        $winners = $this->winnerEvaluator->determineWinners([], $communityCards);

        $this->assertEmpty($winners);
    }

    public function testGetHandEvaluator(): void
    {
        $this->assertSame($this->handEvaluator, $this->winnerEvaluator->getHandEvaluator());
    }
}
