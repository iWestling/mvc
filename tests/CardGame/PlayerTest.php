<?php

namespace App\Tests\CardGame;

use App\CardGame\Player;
use App\CardGame\CardGraphic;
use App\CardGame\IntelligentComputer;
use App\CardGame\NormalComputer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PlayerTest extends TestCase
{
    public function testConstructorSetsPropertiesCorrectly(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $this->assertEquals('Test Player', $player->getName());
        $this->assertEquals(1000, $player->getChips());
        $this->assertInstanceOf(NormalComputer::class, $this->getPlayerStrategy($player)); // Verify strategy is NormalComputer
        $this->assertFalse($player->isFolded());
        $this->assertEquals(0, $player->getCurrentBet());
        $this->assertEmpty($player->getHand());
    }
    public function testConstructorWithInvalidStrategyLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid level: invalid');

        new Player('Test Player', 1000, 'invalid');
    }

    /**
     * Utility method to access the private strategy property of the Player class.
     *
     * @return NormalComputer|IntelligentComputer|null
     */
    private function getPlayerStrategy(Player $player): NormalComputer|IntelligentComputer|null
    {
        $reflection = new ReflectionClass($player);
        $strategyProperty = $reflection->getProperty('strategy');
        $strategyProperty->setAccessible(true);

        $strategy = $strategyProperty->getValue($player);

        // Explicitly cast the result to the expected types
        return $strategy instanceof NormalComputer || $strategy instanceof IntelligentComputer ? $strategy : null;
    }


    public function testAddCardToHand(): void
    {
        $player = new Player('Test Player', 1000, 'normal');
        $card = new CardGraphic(5, 'hearts');

        $player->addCardToHand($card);

        $this->assertCount(1, $player->getHand());
        $this->assertSame($card, $player->getHand()[0]);
    }

    public function testResetHand(): void
    {
        $player = new Player('Test Player', 1000, 'normal');
        $card = new CardGraphic(5, 'hearts');
        $player->addCardToHand($card);

        $player->resetHand();

        $this->assertEmpty($player->getHand());
    }

    public function testSetAndGetChips(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $player->setChips(500);

        $this->assertEquals(500, $player->getChips());
    }

    public function testAddChips(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $player->addChips(200);

        $this->assertEquals(1200, $player->getChips());
    }

    public function testFoldAndUnfold(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $player->fold();
        $this->assertTrue($player->isFolded());

        $player->unfold();
        $this->assertFalse($player->isFolded());
    }

    public function testSetAndGetCurrentBet(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $player->setCurrentBet(300);

        $this->assertEquals(300, $player->getCurrentBet());
    }

    public function testSetAndGetRole(): void
    {
        $player = new Player('Test Player', 1000, 'normal');

        $player->setRole('dealer');
        $this->assertEquals('dealer', $player->getRole());

        $player->setRole(null);
        $this->assertNull($player->getRole());
    }


    public function testMakeDecision(): void
    {
        $player = new Player('Test Player', 1000, 'intelligent');
        $communityCards = [new CardGraphic(5, 'hearts'), new CardGraphic(10, 'spades')];
        $decision = $player->makeDecision($communityCards, 100);

        $this->assertIsString($decision);
        // Optionally check for specific behavior of IntelligentComputer strategy
    }
}
