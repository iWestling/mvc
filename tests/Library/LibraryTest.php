<?php

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class LibraryTest extends TestCase
{
    public function testSetTitle(): void
    {
        $library = new Library();
        $library->setTitle('Sample Title');
        $this->assertSame('Sample Title', $library->getTitle());
    }

    public function testSetAndGetAuthor(): void
    {
        $library = new Library();
        $library->setAuthor('Sample Author');
        $this->assertSame('Sample Author', $library->getAuthor());
    }

    public function testSetAndGetIsbn(): void
    {
        $library = new Library();
        $library->setIsbn('1234567890');
        $this->assertSame('1234567890', $library->getIsbn());
    }

    public function testSetAndGetBookimage(): void
    {
        $library = new Library();
        $library->setBookimage('image.jpg');
        $this->assertSame('image.jpg', $library->getBookimage());
    }

    public function testSetAndGetDescription(): void
    {
        $library = new Library();
        $library->setDescription('Sample Description');
        $this->assertSame('Sample Description', $library->getDescription());
    }
}
