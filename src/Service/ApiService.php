<?php

namespace App\Service;

use App\Repository\LibraryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiService
{
    private LibraryRepository $libraryRepository;

    public function __construct(LibraryRepository $libraryRepository)
    {
        $this->libraryRepository = $libraryRepository;
    }

    public function getAllBooks(): JsonResponse
    {
        // Fetch all books
        $books = $this->libraryRepository->findAll();

        $bookData = [];
        foreach ($books as $book) {
            $bookData[] = [
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'bookimage' => $book->getBookimage(),
                'description' => $book->getDescription(),
            ];
        }

        $response = new JsonResponse($bookData);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    public function getBookByIsbn(string $isbn): JsonResponse
    {
        $book = $this->libraryRepository->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            $response = new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            return $response;
        }

        $bookData = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'bookimage' => $book->getBookimage(),
            'description' => $book->getDescription(),
        ];

        $response = new JsonResponse($bookData);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
