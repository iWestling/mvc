<?php

namespace App\Tests\CardGame;

use App\CardGame\HandRankingEvaluator;
use App\CardGame\CardGraphic;
use PHPUnit\Framework\TestCase;

class HandRankingEvaluatorTest extends TestCase
{
    private HandRankingEvaluator $handRankingEvaluator;

    protected function setUp(): void
    {
        $this->handRankingEvaluator = new HandRankingEvaluator();
    }

    public function testEvaluateHandWithFlush(): void
    {
        $hand = [
            new CardGraphic(2, 'hearts'),
            new CardGraphic(4, 'hearts'),
            new CardGraphic(6, 'hearts'),
            new CardGraphic(8, 'hearts'),
            new CardGraphic(10, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('Flush', $result['rank']);
        $this->assertEquals([10, 8, 6, 4, 2], $result['values']);
    }

    public function testEvaluateHandWithStraight(): void
    {
        $hand = [
            new CardGraphic(2, 'hearts'),
            new CardGraphic(3, 'diamonds'),
            new CardGraphic(4, 'clubs'),
            new CardGraphic(5, 'spades'),
            new CardGraphic(6, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('Straight', $result['rank']);
        $this->assertEquals([6, 5, 4, 3, 2], $result['values']);
    }

    public function testEvaluateHandWithFullHouse(): void
    {
        $hand = [
            new CardGraphic(3, 'hearts'),
            new CardGraphic(3, 'diamonds'),
            new CardGraphic(3, 'clubs'),
            new CardGraphic(5, 'spades'),
            new CardGraphic(5, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('Full House', $result['rank']);
        $this->assertEquals([3, 5], $result['values']); // The values returned by the Full House should be the three of a kind and the pair only
    }

    public function testEvaluateHandWithThreeOfAKind(): void
    {
        $hand = [
            new CardGraphic(4, 'hearts'),
            new CardGraphic(4, 'diamonds'),
            new CardGraphic(4, 'clubs'),
            new CardGraphic(6, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('Three of a Kind', $result['rank']);
        $this->assertEquals([4, 4, 4, 9, 6], $result['values']);
    }

    public function testEvaluateHandWithTwoPair(): void
    {
        $hand = [
            new CardGraphic(5, 'hearts'),
            new CardGraphic(5, 'diamonds'),
            new CardGraphic(9, 'clubs'),
            new CardGraphic(9, 'spades'),
            new CardGraphic(2, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('Two Pair', $result['rank']);
        $this->assertEquals([9, 9, 5, 5, 2], $result['values']);
    }

    public function testEvaluateHandWithOnePair(): void
    {
        $hand = [
            new CardGraphic(7, 'hearts'),
            new CardGraphic(7, 'diamonds'),
            new CardGraphic(10, 'clubs'),
            new CardGraphic(3, 'spades'),
            new CardGraphic(2, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('One Pair', $result['rank']);
        $this->assertEquals([7, 7, 10, 3, 2], $result['values']);
    }

    public function testEvaluateHandWithHighCard(): void
    {
        $hand = [
            new CardGraphic(3, 'hearts'),
            new CardGraphic(7, 'diamonds'),
            new CardGraphic(10, 'clubs'),
            new CardGraphic(2, 'spades'),
            new CardGraphic(5, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('High Card', $result['rank']);
        $this->assertEquals([10, 7, 5, 3, 2], $result['values']);
    }

    public function testIsFlush(): void
    {
        $hand = [
            new CardGraphic(2, 'hearts'),
            new CardGraphic(4, 'hearts'),
            new CardGraphic(6, 'hearts'),
            new CardGraphic(8, 'hearts'),
            new CardGraphic(10, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isFlush($hand);

        $this->assertEquals([10, 8, 6, 4, 2], $result);
    }

    public function testIsStraight(): void
    {
        $hand = [
            new CardGraphic(2, 'hearts'),
            new CardGraphic(3, 'diamonds'),
            new CardGraphic(4, 'clubs'),
            new CardGraphic(5, 'spades'),
            new CardGraphic(6, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isStraight($hand);

        $this->assertEquals([6, 5, 4, 3, 2], $result);
    }

    public function testIsFullHouse(): void
    {
        $hand = [
            new CardGraphic(3, 'hearts'),
            new CardGraphic(3, 'diamonds'),
            new CardGraphic(3, 'clubs'),
            new CardGraphic(5, 'spades'),
            new CardGraphic(5, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isFullHouse($hand);

        $this->assertEquals([3, 5], $result); // Same fix as in evaluateHandWithFullHouse
    }

    public function testIsThreeOfAKind(): void
    {
        $hand = [
            new CardGraphic(4, 'hearts'),
            new CardGraphic(4, 'diamonds'),
            new CardGraphic(4, 'clubs'),
            new CardGraphic(6, 'spades'),
            new CardGraphic(9, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isThreeOfAKind($hand);

        $this->assertEquals([4, 4, 4, 9, 6], $result);
    }

    public function testIsTwoPair(): void
    {
        $hand = [
            new CardGraphic(5, 'hearts'),
            new CardGraphic(5, 'diamonds'),
            new CardGraphic(9, 'clubs'),
            new CardGraphic(9, 'spades'),
            new CardGraphic(2, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isTwoPair($hand);

        $this->assertEquals([9, 9, 5, 5, 2], $result);
    }

    public function testIsOnePair(): void
    {
        $hand = [
            new CardGraphic(7, 'hearts'),
            new CardGraphic(7, 'diamonds'),
            new CardGraphic(10, 'clubs'),
            new CardGraphic(3, 'spades'),
            new CardGraphic(2, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isOnePair($hand);

        $this->assertEquals([7, 7, 10, 3, 2], $result); // Adjusted for correct order
    }

    public function testIsHighCard(): void
    {
        $hand = [
            new CardGraphic(3, 'hearts'),
            new CardGraphic(7, 'diamonds'),
            new CardGraphic(10, 'clubs'),
            new CardGraphic(2, 'spades'),
            new CardGraphic(5, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->evaluateHand($hand);

        $this->assertEquals('High Card', $result['rank']);
        $this->assertEquals([10, 7, 5, 3, 2], $result['values']);
    }
    public function testIsRoyalFlush(): void
    {
        $hand = [
            new CardGraphic(10, 'hearts'),
            new CardGraphic(11, 'hearts'),
            new CardGraphic(12, 'hearts'),
            new CardGraphic(13, 'hearts'),
            new CardGraphic(1, 'hearts'), // Ace treated as 14
        ];

        $result = $this->handRankingEvaluator->isRoyalFlush($hand);
        $this->assertTrue($result, 'The hand should be identified as a Royal Flush.');

        // Test a non-Royal Flush hand
        $nonRoyalFlushHand = [
            new CardGraphic(9, 'hearts'),
            new CardGraphic(10, 'hearts'),
            new CardGraphic(11, 'hearts'),
            new CardGraphic(12, 'hearts'),
            new CardGraphic(13, 'hearts'),
        ];

        $result = $this->handRankingEvaluator->isRoyalFlush($nonRoyalFlushHand);
        $this->assertFalse($result, 'The hand should not be identified as a Royal Flush.');
    }

}
