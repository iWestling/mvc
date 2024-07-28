<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Library;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\LibraryRepository;

use App\Service\ApiService;
use App\Service\DatabaseResetService;

class LibraryController extends AbstractController
{
    private ApiService $apiService;
    private DatabaseResetService $databaseResetService;

    public function __construct(
        ApiService $apiService,
        DatabaseResetService $databaseResetService
    ) {
        $this->apiService = $apiService;
        $this->databaseResetService = $databaseResetService;
    }

    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig');
    }

    #[Route('/library/book/create', name: 'book_create')]
    public function createBook(Request $request, LibraryRepository $libraryRepository): Response
    {
        if ($request->isMethod('POST')) {
            $title = (string) $request->request->get('title');
            $author = (string) $request->request->get('author');
            $isbn = (string) $request->request->get('isbn');
            $image = (string) $request->request->get('image');
            $description = (string) $request->request->get('description');

            if (!$title || !$author || !$isbn) {
                return new Response('Please fill in all required fields', 400);
            }

            // Create a new Library entity and set properties
            $book = new Library();
            $book->setTitle($title);
            $book->setAuthor($author);
            $book->setIsbn($isbn);
            $book->setBookimage($image);
            $book->setDescription($description);

            // Persist the entity
            $libraryRepository->save($book);

            return $this->redirectToRoute('book_by_id', ['bookid' => $book->getId()]);
        }

        return $this->render('library/create.html.twig');
    }


    #[Route('/library/books', name: 'book_show_all')]
    public function showAllBooks(LibraryRepository $libraryRepository): Response
    {
        $books = $libraryRepository->findAll();
        $bookData = [];

        foreach ($books as $book) {
            $bookData[] = [
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'isbn' => $book->getIsbn(),
                'id' => $book->getId(),
            ];
        }

        return $this->render('library/show_all_books.html.twig', [
            'books' => $bookData,
        ]);
    }

    #[Route('/library/book/{bookid}', name: 'book_by_id')]
    public function showBookById(LibraryRepository $libraryRepository, int $bookid): Response
    {
        $book = $libraryRepository->find($bookid);

        if (!$book) {
            throw $this->createNotFoundException('The book does not exist');
        }

        $bookData = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'description' => $book->getDescription(),
            'bookimage' => $book->getBookimage(),
            'id' => $book->getId(),
        ];

        return $this->render('library/show_book.html.twig', [
            'book' => $bookData,
        ]);
    }

    #[Route('/library/book/delete/{bookid}', name: 'book_delete_by_id')]
    public function deleteBookById(LibraryRepository $libraryRepository, int $bookid): Response
    {
        $book = $libraryRepository->find($bookid);

        if (!$book) {
            throw $this->createNotFoundException('No book found for id ' . $bookid);
        }

        $libraryRepository->remove($book);

        return $this->redirectToRoute('book_show_all');
    }

    #[Route('/library/book/update/{bookid}', name: 'book_update')]
    public function updateBook(Request $request, LibraryRepository $libraryRepository, int $bookid): Response
    {
        $book = $libraryRepository->find($bookid);

        if (!$book) {
            throw $this->createNotFoundException('No book found for id ' . $bookid);
        }

        if ($request->isMethod('POST')) {
            $title = (string) $request->request->get('title');
            $author = (string) $request->request->get('author');
            $isbn = (string) $request->request->get('isbn');
            $image = (string) $request->request->get('image');
            $description = (string) $request->request->get('description');

            if (!$title || !$author || !$isbn) {
                return new Response('Please fill in all required fields', 400);
            }

            // Update the book entity with new data
            $book->setTitle($title);
            $book->setAuthor($author);
            $book->setIsbn($isbn);
            $book->setBookimage($image);
            $book->setDescription($description);

            $libraryRepository->save($book);

            return $this->redirectToRoute('book_by_id', ['bookid' => $book->getId()]);
        }

        return $this->render('library/update_book.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/api/library/books', name: 'api_library_books')]
    public function getAllBooks(): JsonResponse
    {
        return $this->apiService->getAllBooks();
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_book')]
    public function getBookByIsbn(string $isbn): JsonResponse
    {
        return $this->apiService->getBookByIsbn($isbn);
    }

    #[Route('/library/reset', name: 'library_reset')]
    public function resetDatabase(): Response
    {
        return $this->databaseResetService->resetDatabase();
    }

}
