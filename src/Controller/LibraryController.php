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
    #[Route('/library', name: 'app_library')]
    public function index(): Response
    {
        return $this->render('library/index.html.twig', [
            'controller_name' => 'LibraryController',
        ]);
    }

    #[Route('/library/create', name: 'book_create', methods: ['GET', 'POST'])]
    public function createBook(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        if ($request->isMethod('POST')) {
            // Retrieve form data from the request
            $title = $request->request->get('title');
            $author = $request->request->get('author');
            $isbn = $request->request->get('isbn');
            $image = $request->request->get('image');
            $description = $request->request->get('description');

            // Basic validation: check if required fields are not empty
            if (!$title || !$author || !$isbn) {
                // Handle validation error, maybe return a response with an error message
                // For example:
                // return new Response('Please fill in all required fields', 400);
            }

            // Create a new Library entity and set its properties
            $book = new Library();
            $book->setTitle((string) $title);
            $book->setAuthor((string) $author);
            $book->setIsbn((string) $isbn);
            $book->setBookimage($image !== null ? (string) $image : null); // Typecast and handle null for nullable field
            $book->setDescription($description !== null ? (string) $description : null); // Typecast and handle null for nullable field

            // Persist the entity to the database
            $entityManager->persist($book);
            $entityManager->flush();

            // Redirect to the page displaying the details of the newly created book
            return $this->redirectToRoute('book_by_id', ['bookid' => $book->getId()]);
        }

        // Render the form
        return $this->render('library/create.html.twig');
    }

    #[Route('/library/show', name: 'book_show_all')]
    public function showAllBooks(
        LibraryRepository $libraryRepository
    ): Response {
        $books = $libraryRepository->findAll();

        return $this->render('library/show_all_books.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/library/show/{id}', name: 'book_by_id')]
    public function showBookById(
        LibraryRepository $libraryRepository,
        int $bookid
    ): Response {
        $book = $libraryRepository->find($bookid);

        return $this->render('library/show_book.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/delete/{id}', name: 'book_delete_by_id')]
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

    #[Route('/book/update/{id}', name: 'book_update')]
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
            // Retrieve form data from the request
            $title = $request->request->get('title');
            $author = $request->request->get('author');
            $isbn = $request->request->get('isbn');
            $image = $request->request->get('image');
            $description = $request->request->get('description');

            // Basic validation: check if required fields are not empty
            if (!$title || !$author || !$isbn) {
                // Handle validation error, maybe return a response with an error message
                // For example:
                // return new Response('Please fill in all required fields', 400);
            }

            // Update the book entity with new data
            $book->setTitle((string) $title);
            $book->setAuthor((string) $author);
            $book->setIsbn((string) $isbn);
            $book->setBookimage($image !== null ? (string) $image : null); // Typecast and handle null for nullable field
            $book->setDescription($description !== null ? (string) $description : null); // Typecast and handle null for nullable field

            // Persist the updated entity to the database
            $entityManager->flush();

            // Redirect to the book detail page
            return $this->redirectToRoute('book_by_id', ['bookid' => $book->getId()]);
        }

        // Render the update book form
        return $this->render('library/update_book.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/api/library/books', name: 'api_library_books')]
    public function getAllBooks(LibraryRepository $libraryRepository): JsonResponse
    {
        // Fetch all books from the repository
        $books = $libraryRepository->findAll();

        // Convert books array to associative array for JSON response
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

        // Return JSON response with pretty print
        $response = new JsonResponse($bookData);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    #[Route('/api/library/book/{isbn}', name: 'api_library_book')]
    public function getBookByIsbn(string $isbn, LibraryRepository $libraryRepository): JsonResponse
    {
        // Fetch book from the repository by ISBN
        $book = $libraryRepository->findOneBy(['isbn' => $isbn]);
    
        // Check if the book was found
        if (!$book) {
            // Return a JSON response indicating that the book was not found
            $response = new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            return $response;
        }
    
        // Convert book object to associative array for JSON response
        $bookData = [
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'bookimage' => $book->getBookimage(),
            'description' => $book->getDescription(),
        ];
    
        // Return JSON response with pretty print
        $response = new JsonResponse($bookData);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
    
        return $response;
    }
    
}
