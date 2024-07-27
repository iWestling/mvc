<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\ReportSiteJson;

class ReportSiteJsonTest extends WebTestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->session->start();
    }

    public function testJsonNumber(): void
    {
        $controller = new ReportSiteJson();
        $response = $controller->jsonNumber();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('lucky-number', $data);
        $this->assertArrayHasKey('lucky-message', $data);
    }

    public function testJsonQuote(): void
    {
        $controller = new ReportSiteJson();
        $response = $controller->jsonQuote();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('quote', $data);
        $this->assertArrayHasKey('date', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    // public function testGetDeck(): void
    // {
    //     $controller = new ReportSiteJson();
    //     $response = $controller->getDeck($this->session);

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $content = $response->getContent();
    //     $this->assertIsString($content);

    //     $data = json_decode($content, true);
    //     $this->assertIsArray($data);
    //     $this->assertNotEmpty($data);
    //     $this->assertArrayHasKey('value', $data[0]);
    //     $this->assertArrayHasKey('card', $data[0]);
    //     $this->assertArrayHasKey('imagepath', $data[0]);

    //     $deck = $this->session->get('deck');
    //     $this->assertIsArray($deck);
    //     $this->assertEquals(count($data), count($deck));
    // }

    // public function testShuffleDeck(): void
    // {
    //     $controller = new ReportSiteJson();
    //     $response = $controller->shuffleDeck($this->session);

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $content = $response->getContent();
    //     $this->assertIsString($content);

    //     $data = json_decode($content, true);
    //     $this->assertIsArray($data);
    //     $this->assertNotEmpty($data);
    //     $this->assertArrayHasKey('value', $data[0]);
    //     $this->assertArrayHasKey('card', $data[0]);
    //     $this->assertArrayHasKey('imagepath', $data[0]);

    //     $deck = $this->session->get('deck');
    //     $this->assertIsArray($deck);
    //     $this->assertEquals(count($data), count($deck));
    // }

    // public function testDrawCardFromDeck(): void
    // {
    //     $controller = new ReportSiteJson();
    //     $controller->getDeck($this->session); // Initialize the deck in session
    //     $response = $controller->drawCardFromDeck($this->session);

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $content = $response->getContent();
    //     $this->assertIsString($content);

    //     $data = json_decode($content, true);
    //     $this->assertIsArray($data);
    //     $this->assertArrayHasKey('drawnCard', $data);
    //     $this->assertArrayHasKey('value', $data['drawnCard']);
    //     $this->assertArrayHasKey('card', $data['drawnCard']);
    //     $this->assertArrayHasKey('imagepath', $data['drawnCard']);
    //     $this->assertArrayHasKey('remainingCards', $data);

    //     $deck = $this->session->get('deck');
    //     $this->assertIsArray($deck);
    //     $this->assertEquals(51, count($deck));
    // }

    // public function testDrawMultipleCardsFromDeck(): void
    // {
    //     $controller = new ReportSiteJson();
    //     $controller->getDeck($this->session); // Initialize the deck in session
    //     $response = $controller->drawMultipleCardsFromDeck($this->session, 3);

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $content = $response->getContent();
    //     $this->assertIsString($content);

    //     $data = json_decode($content, true);
    //     $this->assertIsArray($data);
    //     $this->assertArrayHasKey('drawnCards', $data);
    //     $this->assertCount(3, $data['drawnCards']);
    //     $this->assertArrayHasKey('remainingCards', $data);

    //     $deck = $this->session->get('deck');
    //     $this->assertIsArray($deck);
    //     $this->assertEquals(49, count($deck));
    // }

    // public function testDealCardsToPlayers(): void
    // {
    //     $controller = new ReportSiteJson();
    //     $controller->getDeck($this->session); // Initialize the deck in session
    //     $response = $controller->dealCardsToPlayers(3, 5, $this->session);

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $content = $response->getContent();
    //     $this->assertIsString($content);

    //     $data = json_decode($content, true);
    //     $this->assertIsArray($data);
    //     $this->assertArrayHasKey('playerHands', $data);
    //     $this->assertCount(3, $data['playerHands']);
    //     $this->assertArrayHasKey('remainingCards', $data);

    //     $deck = $this->session->get('deck');
    //     $this->assertIsArray($deck);
    //     $this->assertEquals(52 - (3 * 5), count($deck));
    // }
}
