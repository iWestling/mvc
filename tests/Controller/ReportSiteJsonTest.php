<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\ReportSiteJson;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use App\Card\DeckOfCards;
use App\Card\Card;
use App\Card\CardGraphic;

class ReportSiteJsonTest extends WebTestCase
{
    /**
     * @var MockObject&SessionInterface
     */
    private $sessionMock;

    /**
     * @var ReportSiteJson
     */
    private $controller;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->controller = new ReportSiteJson();
    }

    public function testJsonNumber(): void
    {
        $response = $this->controller->jsonNumber();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('lucky-number', $data);
        $this->assertArrayHasKey('lucky-message', $data);
        $this->assertEquals('Hi there!', $data['lucky-message']);
    }

    public function testJsonQuote(): void
    {
        $response = $this->controller->jsonQuote();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('quote', $data);
        $this->assertArrayHasKey('date', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testDrawCardFromEmptyDeck(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn([]);

        $response = $this->controller->drawCardFromDeck($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No cards in the deck. Please shuffle the deck.', $data['error']);
    }

    public function testDrawMultipleCardsFromEmptyDeck(): void
    {
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn([]);

        $response = $this->controller->drawMultipleCardsFromDeck($this->sessionMock, 5);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No cards in the deck. Please shuffle the deck.', $data['error']);
    }

    public function testDrawMultipleCardsInvalidNumber(): void
    {
        $deck = [new Card(1), new Card(2), new Card(3)];

        // Ensure the deck is not empty so that the invalid number check is triggered
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn($deck);

        // Case where the number of cards to draw is invalid (<= 0)
        $response = $this->controller->drawMultipleCardsFromDeck($this->sessionMock, 0);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid number of cards to draw.', $data['error']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
    public function testDrawMultipleCardsSuccessfully(): void
    {
        // Create a mock deck with 5 cards
        $deck = [
            new Card(1), // Ace of Hearts
            new Card(2), // 2 of Hearts
            new Card(3), // 3 of Hearts
            new Card(4), // 4 of Hearts
            new Card(5)  // 5 of Hearts
        ];

        // Mock the session to return the deck
        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn($deck);

        // Mock the session to save the updated deck after drawing cards
        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', $this->isType('array'));

        // Draw 3 cards from the deck
        $response = $this->controller->drawMultipleCardsFromDeck($this->sessionMock, 3);
        $this->assertInstanceOf(JsonResponse::class, $response);

        // Decode the JSON response
        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        // Check if JSON decoding was successful
        $data = json_decode($content, true);
        $this->assertNotFalse($data, 'Failed to decode JSON response');
        $this->assertIsArray($data, 'Response is not an array');

        // Check that the correct number of cards were drawn
        $this->assertArrayHasKey('drawnCards', $data);
        $this->assertIsArray($data['drawnCards']);
        $this->assertCount(3, $data['drawnCards']);

        // Check the remaining cards in the deck
        $this->assertArrayHasKey('remainingCards', $data);
        $this->assertEquals(2, $data['remainingCards']);

        // Validate the drawn cards
        $expectedDrawnCards = [
            ['value' => 1, 'card' => '[A♥]', 'imagepath' => 'img/carddeck/hearts_ace.png'],
            ['value' => 2, 'card' => '[2♥]', 'imagepath' => 'img/carddeck/hearts_2.png'],
            ['value' => 3, 'card' => '[3♥]', 'imagepath' => 'img/carddeck/hearts_3.png']
        ];
        $this->assertEquals($expectedDrawnCards, $data['drawnCards']);
    }

    public function testGetDeck(): void
    {
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', $deck);

        $response = $this->controller->getDeck($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertCount(52, $data); // There should be 52 cards in the deck
        $this->assertArrayHasKey('value', $data[0]);
        $this->assertArrayHasKey('card', $data[0]);
        $this->assertArrayHasKey('imagepath', $data[0]);
    }

    public function testShuffleDeck(): void
    {
        $deckOfCards = new DeckOfCards();
        $deck = $deckOfCards->getCards();

        // Expect 'set' to be called twice, once before and once after shuffle
        /** @scrutinizer ignore-deprecated */ $this->sessionMock->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['deck', $deck],
                ['deck', $this->isType('array')]
            );

        $response = $this->controller->shuffleDeck($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertCount(52, $data); // There should be 52 cards after shuffling
    }

    public function testDrawCard(): void
    {
        $deck = [new Card(1), new Card(2), new Card(3)];

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn($deck);

        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', [new Card(2), new Card(3)]);

        $response = $this->controller->drawCardFromDeck($this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('drawnCard', $data);
        $this->assertArrayHasKey('remainingCards', $data);
        $this->assertEquals(2, $data['remainingCards']); // Two cards should remain after drawing one
    }

    public function testDealCardsToPlayers(): void
    {
        $deck = [new Card(1), new Card(2), new Card(3), new Card(4), new Card(5), new Card(6)];

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn($deck);

        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('deck', []);

        $response = $this->controller->dealCardsToPlayers(2, 3, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('playerHands', $data);
        $this->assertCount(2, $data['playerHands']); // Two players
        $this->assertCount(3, $data['playerHands']['Player 1']); // Each player has 3 cards
    }

    public function testDealCardsToPlayersWithInvalidNumbers(): void
    {
        // Ensure the deck is not empty to test invalid player/card numbers properly
        $deck = [new Card(1), new Card(2), new Card(3)];

        $this->sessionMock->expects($this->once())
            ->method('get')
            ->with('deck', [])
            ->willReturn($deck);

        $response = $this->controller->dealCardsToPlayers(0, 3, $this->sessionMock);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $data = json_decode($content !== false ? $content : '', true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid number of players or cards.', $data['error']);
    }
}
