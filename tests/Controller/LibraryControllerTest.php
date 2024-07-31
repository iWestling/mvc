<?php

namespace App\Tests\Controller;

use App\Controller\LibraryController;
use App\Service\ApiService;
use App\Service\DatabaseResetService;
use App\Repository\LibraryRepository;
use App\Entity\Library;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class LibraryControllerTest extends WebTestCase
{
    /** @var MockObject&ApiService */
    private MockObject $apiServiceMock;
    /** @var MockObject&DatabaseResetService */
    private MockObject $dbResetServiceMock;

    protected function setUp(): void
    {
        $this->apiServiceMock = $this->createMock(ApiService::class);
        $this->dbResetServiceMock = $this->createMock(DatabaseResetService::class);
    }

    public function testIndex(): void
    {
        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('library/index.html.twig')
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->index();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowAllBooks(): void
    {
        $libRepoMock = $this->createMock(LibraryRepository::class);

        $libRepoMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$this->createMock(Library::class)]);

        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('library/show_all_books.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->showAllBooks($libRepoMock);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowBookById(): void
    {
        $libRepoMock = $this->createMock(LibraryRepository::class);
        $library = $this->createMock(Library::class);

        $libRepoMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($library);

        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('library/show_book.html.twig', $this->isType('array'))
            ->willReturn(new Response());

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->showBookById($libRepoMock, 1);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testDeleteBookById(): void
    {
        $libRepoMock = $this->createMock(LibraryRepository::class);
        $library = $this->createMock(Library::class);

        $libRepoMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($library);

        $libRepoMock->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($library));

        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('book_show_all')
            ->willReturn(new RedirectResponse('/library/books'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->deleteBookById($libRepoMock, 1);

        $this->assertNotNull($response);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testGetAllBooks(): void
    {
        $this->apiServiceMock->expects($this->once())
            ->method('getAllBooks')
            ->willReturn(new JsonResponse());

        $controller = new LibraryController($this->apiServiceMock, $this->dbResetServiceMock);

        $response = $controller->getAllBooks();

        $this->assertNotNull($response);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testGetBookByIsbn(): void
    {
        $this->apiServiceMock->expects($this->once())
            ->method('getBookByIsbn')
            ->with('1234567890')
            ->willReturn(new JsonResponse());

        $controller = new LibraryController($this->apiServiceMock, $this->dbResetServiceMock);

        $response = $controller->getBookByIsbn('1234567890');

        $this->assertNotNull($response);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testResetDatabase(): void
    {
        $this->dbResetServiceMock->expects($this->once())
            ->method('resetDatabase')
            ->willReturn(new Response());

        $controller = new LibraryController($this->apiServiceMock, $this->dbResetServiceMock);

        $response = $controller->resetDatabase();

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}
