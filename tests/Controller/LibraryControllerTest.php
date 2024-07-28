<?php

namespace App\Tests\Controller;

use App\Entity\Library;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        // Simulate a GET request to the index page
        $crawler = $client->request('GET', '/library');

        // Assert the response is successful
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert the page contains expected content
        $this->assertStringContainsString('Library', $crawler->filter('h1')->text());
        // You can add more specific assertions here based on your HTML structure
    }

    public function testCreateBook(): void
    {
        $client = static::createClient();

        // Simulate a POST request to create a book
        $crawler = $client->request('POST', '/library/book/create', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890',
            'description' => 'This is a test book',
        ]);

        // Check if the response is a redirect (HTTP status code 302)
        $this->assertTrue($client->getResponse()->isRedirect());

        // Follow the redirect
        $crawler = $client->followRedirect();

        // Check if the redirected page is successful (HTTP status code 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check if the book details are displayed correctly on the redirected page
        $this->assertStringContainsString('Test Book', $crawler->filter('h1')->text());
    }

    public function testShowAllBooks(): void
    {
        $client = static::createClient();

        // Simulate a GET request to show all books
        $crawler = $client->request('GET', '/library/books');

        // Check if the response is successful (HTTP status code 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check if the page contains expected content
        $this->assertStringContainsString('All Books', $crawler->filter('h1')->text());
        // You can add more specific assertions here based on your HTML structure
    }

    public function testShowBookById(): void
    {
        // Create a new book entry in the test database
        $client = static::createClient();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $book = new Library();
        $book->setTitle('Test Book');
        $book->setAuthor('Test Author');
        $book->setIsbn('1234567890'); // Set a dummy ISBN
        // Set other properties as needed
        $entityManager->persist($book);
        $entityManager->flush();

        // Retrieve the ID of the newly created book
        $bookId = $book->getId();

        // Use the retrieved book ID in the request URL
        $client->request('GET', '/library/book/' . $bookId);

        // Assert the response
        $this->assertResponseIsSuccessful();
        // You can add more specific assertions here based on your HTML structure
    }

    public function testDeleteBookById(): void
    {
        $client = static::createClient();

        // Create a new book entry in the test database
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $book = new Library();
        $book->setTitle('Test Book');
        $book->setAuthor('Test Author');
        $book->setIsbn('1234567890'); // Set a dummy ISBN
        // Set other properties as needed
        $entityManager->persist($book);
        $entityManager->flush();

        // Retrieve the ID of the newly created book
        $bookId = $book->getId();

        // Simulate a DELETE request to delete the book
        $client->request('DELETE', '/library/book/delete/' . $bookId);

        // Assert the response
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertEquals('/library/books', $client->getResponse()->headers->get('Location'));
    }

    public function testUpdateBook(): void
    {
        $client = static::createClient();

        // Create a new book entry in the test database
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $book = new Library();
        $book->setTitle('Test Book');
        $book->setAuthor('Test Author');
        $book->setIsbn('1234567890'); // Set a dummy ISBN
        // Set other properties as needed
        $entityManager->persist($book);
        $entityManager->flush();

        // Retrieve the ID of the newly created book
        $bookId = $book->getId();

        // Simulate a POST request to update the book
        $client->request('POST', '/library/book/update/' . $bookId, [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '0987654321', // Set a new ISBN
            'description' => 'Updated description',
        ]);

        // Assert the response
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertEquals('/library/book/' . $bookId, $client->getResponse()->headers->get('Location'));

        // Fetch the updated book from the database
        $updatedBook = $entityManager->getRepository(Library::class)->find($bookId);

        // Ensure $updatedBook is not null before asserting
        $this->assertNotNull($updatedBook);
        if ($updatedBook !== null) {
            // Assert that the book details have been updated
            $this->assertEquals('Updated Title', $updatedBook->getTitle());
            $this->assertEquals('Updated Author', $updatedBook->getAuthor());
            $this->assertEquals('0987654321', $updatedBook->getIsbn());
            $this->assertEquals('Updated description', $updatedBook->getDescription());
        }
    }

    public function testGetAllBooks(): void
    {
        $client = static::createClient();

        // Simulate a GET request to fetch all books via API
        $client->request('GET', '/api/library/books');

        // Assert the response
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Assert the content type is JSON
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));

        // Decode the JSON response
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent !== false ? $responseContent : '', true);

        // Assert that the response data is an array
        $this->assertIsArray($responseData);

        // Assert that each book entry contains expected keys
        foreach ($responseData as $book) {
            $this->assertArrayHasKey('title', $book);
            $this->assertArrayHasKey('author', $book);
            $this->assertArrayHasKey('isbn', $book);
            $this->assertArrayHasKey('bookimage', $book);
            $this->assertArrayHasKey('description', $book);
        }
    }

}
