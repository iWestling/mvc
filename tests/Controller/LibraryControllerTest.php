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

    public function testCreateBookFormRendering(): void
    {
        // Create a mock of the LibraryRepository (even if it's not used in this specific test)
        $libRepoMock = $this->createMock(LibraryRepository::class);

        // Create a controller instance
        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        // Expect the `render` method to be called once, returning the form view
        $controller->expects($this->once())
            ->method('render')
            ->with('library/create.html.twig')
            ->willReturn(new Response('Rendered content'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate a GET request (which should render the form)
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = $controller->createBook($request, $libRepoMock);

        // Assert that the response is a normal Response (not a redirect)
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateBookWithPost(): void
    {
        // Create a mock of the LibraryRepository
        $libRepoMock = $this->createMock(LibraryRepository::class);

        // Create a controller instance
        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate a POST request (which should be handled and check if the logic continues)
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $response = $controller->createBook($request, $libRepoMock);

        // Assert that the response is a normal Response
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUpdateBookFormRendering(): void
    {
        // Create a mock of the LibraryRepository
        $libRepoMock = $this->createMock(LibraryRepository::class);

        // Create a mock of the Library entity
        $bookMock = $this->createMock(Library::class);

        // Mock the find method to return the book entity
        $libRepoMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($bookMock);

        // Create a controller instance
        $controller = $this->getMockBuilder(LibraryController::class)
            ->setConstructorArgs([$this->apiServiceMock, $this->dbResetServiceMock])
            ->onlyMethods(['render'])
            ->getMock();

        // Expect the `render` method to be called once, returning the form view
        $controller->expects($this->once())
            ->method('render')
            ->with('library/update_book.html.twig', $this->isType('array'))
            ->willReturn(new Response('Rendered content'));

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        // Simulate a GET request (which should render the form)
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $response = $controller->updateBook($request, $libRepoMock, 1);

        // Assert that the response is a normal Response (not a redirect)
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
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
