<?php

namespace App\Tests\Service;

use App\Repository\LibraryRepository;
use App\Service\DatabaseResetService;
use App\Entity\Library;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

class DatabaseResetServiceTest extends TestCase
{
    /** @var MockObject&LibraryRepository */
    private $libraryRepository;
    private DatabaseResetService $databaseResetService;

    protected function setUp(): void
    {
        $this->libraryRepository = $this->createMock(LibraryRepository::class);
        $this->databaseResetService = new DatabaseResetService($this->libraryRepository);
    }

    public function testResetDatabase(): void
    {
        $this->libraryRepository->expects($this->once())
            ->method('deleteAll');

        $this->libraryRepository->expects($this->exactly(4))
            ->method('save')
            ->with($this->isInstanceOf(Library::class));

        $response = $this->databaseResetService->resetDatabase();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Database reset successful', $response->getContent());
    }
}
