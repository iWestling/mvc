<?php

namespace App\Tests\Controller;

use App\Controller\LuckyController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LuckyControllerTest extends WebTestCase
{
    public function testNumber(): void
    {
        $controller = new LuckyController();
        $response = $controller->number();

        $this->assertInstanceOf(Response::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertMatchesRegularExpression('/Lucky number: \d{1,3}/', $content);

        $number = (int) filter_var($content, FILTER_SANITIZE_NUMBER_INT);
        $this->assertGreaterThanOrEqual(0, $number);
        $this->assertLessThanOrEqual(100, $number);
    }

    public function testHiya(): void
    {
        $controller = new LuckyController();
        $response = $controller->hiya();

        $this->assertInstanceOf(Response::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertSame('<html><body>Hi to you!</body></html>', $content);
    }
}
