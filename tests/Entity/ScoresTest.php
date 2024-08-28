<?php

namespace App\Tests\Entity;

use App\Entity\Scores;
use App\Entity\GamePlayer;
use PHPUnit\Framework\TestCase;
use DateTime;

class ScoresTest extends TestCase
{
    public function testInitialValues(): void
    {
        $score = new Scores();

        $this->assertNull($score->getId());
        $this->assertNull($score->getUserId());
        $this->assertNull($score->getScore());
        $this->assertNull($score->getDate());
    }

    public function testSetAndGetUserId(): void
    {
        $score = new Scores();
        $player = $this->createMock(GamePlayer::class);

        $score->setUserId($player);

        $this->assertSame($player, $score->getUserId());
    }

    public function testSetAndGetScore(): void
    {
        $score = new Scores();
        $scoreValue = 100;

        $score->setScore($scoreValue);

        $this->assertEquals($scoreValue, $score->getScore());
    }

    public function testSetAndGetDate(): void
    {
        $score = new Scores();
        $date = new DateTime();

        $score->setDate($date);

        $this->assertSame($date, $score->getDate());
    }

    public function testScoreRelationshipWithGamePlayer(): void
    {
        $score = new Scores();
        $player = new GamePlayer();

        $score->setUserId($player);
        $this->assertSame($player, $score->getUserId());

        $scoreValue = 200;
        $date = new DateTime('2024-01-01');

        $score->setScore($scoreValue);
        $score->setDate($date);

        $this->assertEquals($scoreValue, $score->getScore());
        $this->assertSame($date, $score->getDate());
    }
}
