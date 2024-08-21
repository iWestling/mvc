<?php

namespace App\Tests\Entity;

use App\Entity\GamePlayer;
use App\Entity\Scores;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GamePlayerTest extends TestCase
{
    public function testInitialValues(): void
    {
        $player = new GamePlayer();

        $this->assertNull($player->getId());
        $this->assertNull($player->getUsername());
        $this->assertNull($player->getAge());
        $this->assertInstanceOf(Collection::class, $player->getScores());
        $this->assertCount(0, $player->getScores());
    }

    public function testGetId(): void
    {
        $player = new GamePlayer();
        $this->assertNull($player->getId());

        // Set the ID manually to test the getter
        $reflection = new ReflectionClass(GamePlayer::class);
        $property = $reflection->getProperty('idn');
        $property->setAccessible(true);
        $property->setValue($player, 1);

        $this->assertEquals(1, $player->getId());
    }
    public function testGetUsername(): void
    {
        $player = new GamePlayer();
        $this->assertNull($player->getUsername());

        // Set the username manually to test the getter
        $reflection = new ReflectionClass(GamePlayer::class);
        $property = $reflection->getProperty('username');
        $property->setAccessible(true);
        $property->setValue($player, 'TestUser');

        $this->assertEquals('TestUser', $player->getUsername());
    }
    public function testSetId(): void
    {
        $player = new GamePlayer();
        $player->setId(1);

        $this->assertEquals(1, $player->getId());
    }

    public function testGetAge(): void
    {
        $player = new GamePlayer();
        $this->assertNull($player->getAge());

        // Set the age manually to test the getter
        $reflection = new ReflectionClass(GamePlayer::class);
        $property = $reflection->getProperty('age');
        $property->setAccessible(true);
        $property->setValue($player, 25);

        $this->assertEquals(25, $player->getAge());
    }

    public function testGetScores(): void
    {
        $player = new GamePlayer();

        $this->assertInstanceOf(Collection::class, $player->getScores());
        $this->assertCount(0, $player->getScores());

        $score = $this->createMock(Scores::class);
        $player->addScore($score);

        $this->assertCount(1, $player->getScores());
        $this->assertTrue($player->getScores()->contains($score));
    }
    public function testSetAndGetUsername(): void
    {
        $player = new GamePlayer();
        $username = 'TestPlayer';
        $player->setUsername($username);

        $this->assertEquals($username, $player->getUsername());
    }

    public function testSetAndGetAge(): void
    {
        $player = new GamePlayer();
        $age = 30;
        $player->setAge($age);

        $this->assertEquals($age, $player->getAge());
    }

    public function testGetScoresReturnsCollection(): void
    {
        $player = new GamePlayer();
        $this->assertInstanceOf(Collection::class, $player->getScores());
        $this->assertCount(0, $player->getScores());
    }

    public function testAddScore(): void
    {
        $player = new GamePlayer();
        $score = $this->getMockBuilder(Scores::class)->getMock();

        $score->expects($this->once())
            ->method('setUserId')
            ->with($player);

        $player->addScore($score);

        $this->assertCount(1, $player->getScores());
        $this->assertTrue($player->getScores()->contains($score));
    }

}
