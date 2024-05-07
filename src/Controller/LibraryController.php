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

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    #[Route('/library/book/create', name: 'book_create')]
    public function createBook(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $author = $request->request->get('author');
            $isbn = $request->request->get('isbn');
            $image = $request->request->get('image');
            $description = $request->request->get('description');

            if (!$title || !$author || !$isbn) {
                return new Response('Please fill in all required fields', 400);
            }

            // Create a new Library entity and set properties
            $book = new Library();
            $book->setTitle((string) $title);
            $book->setAuthor((string) $author);
            $book->setIsbn((string) $isbn);
            $book->setBookimage($image !== null ? (string) $image : null);
            $book->setDescription($description !== null ? (string) $description : null);

            $entityManager->persist($book);
            $entityManager->flush();

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
    public function deleteBookById(
        ManagerRegistry $doctrine,
        int $bookid
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Library::class)->find($bookid);

        if (!$book) {
            throw $this->createNotFoundException(
                'No book found for id '.$bookid
            );
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_show_all');
    }

    #[Route('/library/book/update/{bookid}', name: 'book_update')]
    public function updateBook(Request $request, ManagerRegistry $doctrine, int $bookid): Response
    {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Library::class)->find($bookid);

        if (!$book) {
            throw $this->createNotFoundException(
                'No book found for id '.$bookid
            );
        }

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $author = $request->request->get('author');
            $isbn = $request->request->get('isbn');
            $image = $request->request->get('image');
            $description = $request->request->get('description');

            if (!$title || !$author || !$isbn) {
                return new Response('Please fill in all required fields', 400);
            }

            // Update the book entity with new data
            $book->setTitle((string) $title);
            $book->setAuthor((string) $author);
            $book->setIsbn((string) $isbn);
            $book->setBookimage($image !== null ? (string) $image : null);
            $book->setDescription($description !== null ? (string) $description : null);

            $entityManager->flush();

            // Redirect to the book detail page
            return $this->redirectToRoute('book_by_id', ['bookid' => $book->getId()]);
        }

        return $this->render('library/update_book.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/api/library/books', name: 'api_library_books')]
    public function getAllBooks(LibraryRepository $libraryRepository): JsonResponse
    {
        // Fetch all books
        $books = $libraryRepository->findAll();

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

    #[Route('/api/library/book/{isbn}', name: 'api_library_book')]
    public function getBookByIsbn(string $isbn, LibraryRepository $libraryRepository): JsonResponse
    {
        // Fetch book by ISBN
        $book = $libraryRepository->findOneBy(['isbn' => $isbn]);

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

    #[Route('/library/reset', name: 'library_reset')]
    public function resetDatabase(LibraryRepository $libraryRepository): Response
    {
        // Load original data
        $originalData = [
            ['The Three-Body Problem', 'Liu Cixin', '978-7-536-69293-0', 'the_three_body_problem.jpg', 'The first novel in the Remembrance of Earth\'s Past trilogy'],
            ['The Dark Forest', 'Liu Cixin', '978-1784971595', 'the_dark_forest.jpg', 'The sequel to The Three-Body Problem in the trilogy Remembrance of Earth\'s Past.'],
            ['Death\'s End', 'Liu Cixin', '978-0765377104', 'deaths_end.jpg', 'It\'s the third novel in the trilogy titled Remembrance of Earth\'s Past.'],
            ['Test', 'jh3', '32535', 'test.jpg', 'test']
        ];

        // Clear existing data
        $libraryRepository->deleteAll();

        // Insert original data
        foreach ($originalData as $data) {
            $book = new Library();
            $book->setTitle($data[0]);
            $book->setAuthor($data[1]);
            $book->setIsbn($data[2]);
            $book->setBookimage($data[3]);
            $book->setDescription($data[4]);
            $libraryRepository->save($book);
        }

        return $this->redirectToRoute('book_show_all');
    }
}
