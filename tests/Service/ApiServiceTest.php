<?php

namespace App\Tests\Service;

use App\Repository\LibraryRepository;
use App\Service\ApiService;
use App\Entity\Library;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiServiceTest extends TestCase
{
    /** @var MockObject&LibraryRepository */
    private $libraryRepository;
    private ApiService $apiService;

    protected function setUp(): void
    {
        $this->libraryRepository = $this->createMock(LibraryRepository::class);
        $this->apiService = new ApiService($this->libraryRepository);
    }

    public function testGetAllBooks(): void
    {
        $libraryMock = $this->createMock(Library::class);
        $libraryMock->method('getTitle')->willReturn('Test Title');
        $libraryMock->method('getAuthor')->willReturn('Test Author');
        $libraryMock->method('getIsbn')->willReturn('1234567890');
        $libraryMock->method('getBookimage')->willReturn('test_image.jpg');
        $libraryMock->method('getDescription')->willReturn('Test Description');

        $this->libraryRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$libraryMock]);

        $response = $this->apiService->getAllBooks();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Test Title', $data[0]['title']);
    }

    public function testGetBookByIsbn(): void
    {
        $libraryMock = $this->createMock(Library::class);
        $libraryMock->method('getTitle')->willReturn('Test Title');
        $libraryMock->method('getAuthor')->willReturn('Test Author');
        $libraryMock->method('getIsbn')->willReturn('1234567890');
        $libraryMock->method('getBookimage')->willReturn('test_image.jpg');
        $libraryMock->method('getDescription')->willReturn('Test Description');

        $this->libraryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['isbn' => '1234567890'])
            ->willReturn($libraryMock);

        $response = $this->apiService->getBookByIsbn('1234567890');

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals('Test Title', $data['title']);
    }

    public function testGetBookByIsbnNotFound(): void
    {
        $this->libraryRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['isbn' => '1234567890'])
            ->willReturn(null);

        $response = $this->apiService->getBookByIsbn('1234567890');

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Book not found', $data['error']);
    }
}
