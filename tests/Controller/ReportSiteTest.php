<?php

namespace App\Tests\Controller;

use App\Controller\ReportSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

class ReportSiteTest extends WebTestCase
{
    /**
     * @var MockObject&ReportSite
     */
    private $controller;

    /**
     * @var MockObject&ContainerInterface
     */
    private $containerMock;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->controller = $this->getMockBuilder(ReportSite::class)
            ->onlyMethods(['render'])
            ->getMock();
        $this->controller->setContainer($this->containerMock);
    }

    public function testNumber(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('lucky_number.html.twig', $this->isType('array'))
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->number();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testHome(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('home.html.twig')
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->home();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testAbout(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('about.html.twig')
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->about();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testReport(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('report.html.twig')
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->report();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testMetrics(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('metrics.html.twig')
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->metrics();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testProj(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('texas/index.html.twig')
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->proj();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testApiHome(): void
    {
        $this->controller->expects($this->once())
            ->method('render')
            ->with('api.html.twig', $this->isType('array'))
            ->willReturn(new Response('rendered template'));

        $response = $this->controller->apiHome();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
}
