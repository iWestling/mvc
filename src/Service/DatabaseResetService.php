<?php

namespace App\Service;

use App\Entity\Library;
use App\Repository\LibraryRepository;

use Symfony\Component\HttpFoundation\Response;

class DatabaseResetService
{
    private LibraryRepository $libraryRepository;

    public function __construct(LibraryRepository $libraryRepository)
    {
        $this->libraryRepository = $libraryRepository;
    }

    public function resetDatabase(): Response
    {
        // Load original data
        $originalData = [
            ['The Three-Body Problem', 'Liu Cixin', '978-7-536-69293-0', 'the_three_body_problem.jpg', 'The first novel in the Remembrance of Earth\'s Past trilogy'],
            ['The Dark Forest', 'Liu Cixin', '978-1784971595', 'the_dark_forest.jpg', 'The sequel to The Three-Body Problem in the trilogy Remembrance of Earth\'s Past.'],
            ['Death\'s End', 'Liu Cixin', '978-0765377104', 'deaths_end.jpg', 'It\'s the third novel in the trilogy titled Remembrance of Earth\'s Past.'],
            ['Test', 'jh3', '32535', 'test.jpg', 'test']
        ];

        // Clear existing data
        $this->libraryRepository->deleteAll();

        // Insert original data
        foreach ($originalData as $data) {
            $book = new Library();
            $book->setTitle($data[0]);
            $book->setAuthor($data[1]);
            $book->setIsbn($data[2]);
            $book->setBookimage($data[3]);
            $book->setDescription($data[4]);
            $this->libraryRepository->save($book);
        }

        return new Response('Database reset successful');
    }
}
