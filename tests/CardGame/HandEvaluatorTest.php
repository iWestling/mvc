<?php

namespace App\Tests\CardGame;

use App\CardGame\HandEvaluator;
use App\CardGame\CardGraphic;
use App\CardGame\HandRankingEvaluator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use UnexpectedValueException;

class HandEvaluatorTest extends TestCase
{
    private HandEvaluator $handEvaluator;

    /** @var MockObject&HandRankingEvaluator */
    private MockObject $mockRankingEvaluator;

    protected function setUp(): void
    {
        $this->mockRankingEvaluator = $this->createMock(HandRankingEvaluator::class);

        $this->handEvaluator = new HandEvaluator($this->mockRankingEvaluator);
    }

    public function testGetBestHand(): void
    {
        $cards = [
            new CardGraphic(1, 'hearts'),
            new CardGraphic(2, 'diamonds'),
            new CardGraphic(3, 'clubs'),
            new CardGraphic(4, 'spades'),
            new CardGraphic(5, 'hearts'),
            new CardGraphic(6, 'diamonds'),
            new CardGraphic(7, 'clubs'),
        ];

        $this->mockRankingEvaluator->method('evaluateHand')
            ->willReturn(['rank' => 'Straight', 'values' => [5]]);

        $bestHand = $this->handEvaluator->getBestHand($cards);

        $this->assertEquals('Straight', $bestHand['rank']);
        $this->assertEquals([5], $bestHand['values']);
    }

    public function testCompareHands(): void
    {
        $hand1 = ['rank' => 'Full House', 'values' => [10, 3]];
        $hand2 = ['rank' => 'Flush', 'values' => [10, 4]];

        $result = $this->handEvaluator->compareHands($hand1, $hand2);

        $this->assertEquals(1, $result);
    }

    public function testCompareHandsWithSameRank(): void
    {
        $hand1 = ['rank' => 'One Pair', 'values' => [10, 3]];
        $hand2 = ['rank' => 'One Pair', 'values' => [10, 4]];

        $result = $this->handEvaluator->compareHands($hand1, $hand2);

        $this->assertEquals(-1, $result);
    }

    public function testCompareHandsWithIdenticalValues(): void
    {
        $hand1 = ['rank' => 'One Pair', 'values' => [10, 4]];
        $hand2 = ['rank' => 'One Pair', 'values' => [10, 4]];

        $result = $this->handEvaluator->compareHands($hand1, $hand2);

        $this->assertEquals(0, $result);
    }

    public function testCombinations(): void
    {
        $cards = [
            new CardGraphic(1, 'hearts'),
            new CardGraphic(2, 'diamonds'),
            new CardGraphic(3, 'clubs'),
            new CardGraphic(4, 'spades'),
            new CardGraphic(5, 'hearts'),
        ];

        $combinations = $this->handEvaluator->combinations($cards, 3);

        $this->assertCount(10, $combinations); // Combination of 5 choose 3 is 10
        $this->assertCount(3, $combinations[0]); // Each combination should have 3 cards
    }

    public function testCompareHandValues(): void
    {
        $values1 = [10, 8, 7];
        $values2 = [10, 8, 6];

        $result = $this->invokeCompareHandValues($values1, $values2);

        $this->assertEquals(1, $result);
    }

    public function testCompareHandValuesWithEqualHands(): void
    {
        $values1 = [10, 8, 7];
        $values2 = [10, 8, 7];

        $result = $this->invokeCompareHandValues($values1, $values2);

        $this->assertEquals(0, $result);
    }

    /**
     * @param int[] $values1
     * @param int[] $values2
     */
    private function invokeCompareHandValues(array $values1, array $values2): int
    {
        $reflection = new ReflectionClass(HandEvaluator::class);
        $method = $reflection->getMethod('compareHandValues');
        $method->setAccessible(true);

        $result = $method->invoke($this->handEvaluator, $values1, $values2);

        if (!is_int($result)) {
            throw new UnexpectedValueException('Expected an integer from compareHandValues.');
        }

        return $result;
    }

}
