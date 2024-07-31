<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\ReportSiteJson;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
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

}
