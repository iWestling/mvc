<?php

namespace App\Tests\CardGame;

use App\CardGame\GameManagerJson;
use App\CardGame\Player;
use App\CardGame\TexasHoldemGame;
use PHPUnit\Framework\TestCase;
use App\CardGame\CardGraphic;
use App\CardGame\CommunityCardManager;
use App\CardGame\Deck;
use ReflectionClass;

class GameManagerJsonTest extends TestCase
{
    /**
     * @var GameManagerJson
     */
    private $gameManager;

    protected function setUp(): void
    {
        $this->gameManager = new GameManagerJson();
    }

    public function testStartNewGame(): void
    {
        $chips = 1000;
        $level1 = 'normal';
        $level2 = 'intelligent';

        // Call startNewGame method
        $game = $this->gameManager->startNewGame($chips, $level1, $level2);

        $this->assertInstanceOf(TexasHoldemGame::class, $game);

        $this->assertCount(3, $game->getPlayers());

        $players = $game->getPlayers();
        $this->assertEquals('You', $players[0]->getName());
        $this->assertEquals($chips, $players[0]->getChips());

        // Assert that the second and third players are the computer players with correct levels
        $this->assertEquals('Computer 1', $players[1]->getName());
        $this->assertEquals('Computer 2', $players[2]->getName());
    }

    public function testGetGameState(): void
    {
        $game = new TexasHoldemGame();
        $game->addPlayer(new Player('You', 1000, 'intelligent'));

        $gameState = $this->gameManager->getGameState($game);

        $this->assertIsArray($gameState);

        $this->assertArrayHasKey('players', $gameState);
        $this->assertArrayHasKey('community_cards', $gameState);
        $this->assertArrayHasKey('pot', $gameState);
    }
    public function testGetCommunityCards(): void
    {
        $deck = $this->createMock(Deck::class);
        $communityCardManager = new CommunityCardManager($deck);

        $card1 = new CardGraphic(10, 'hearts');
        $card2 = new CardGraphic(12, 'spades');
        $card3 = new CardGraphic(5, 'diamonds');

        $communityCardManager->dealCommunityCards(0); // reset cards
        $reflection = new ReflectionClass($communityCardManager);
        $property = $reflection->getProperty('communityCards');
        $property->setAccessible(true);
        $property->setValue($communityCardManager, [$card1, $card2, $card3]);

        // Create a game and inject the CommunityCardManager
        $game = new TexasHoldemGame();
        $reflectionGame = new ReflectionClass($game);
        $propertyGame = $reflectionGame->getProperty('communityCardManager');
        $propertyGame->setAccessible(true);
        $propertyGame->setValue($game, $communityCardManager);

        $communityCards = $this->gameManager->getCommunityCards($game);

        $this->assertIsArray($communityCards);
        $this->assertCount(3, $communityCards);
        $this->assertEquals('img/carddeck/hearts_10.png', $communityCards[0]);
        $this->assertEquals('img/carddeck/spades_queen.png', $communityCards[1]);
        $this->assertEquals('img/carddeck/diamonds_5.png', $communityCards[2]);
    }

}
