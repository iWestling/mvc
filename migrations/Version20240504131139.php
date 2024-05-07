<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504131139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__library AS SELECT id, title, author, isbn, bookimage, description FROM library');
        $this->addSql('DROP TABLE library');
        $this->addSql('CREATE TABLE library (bookid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, bookimage VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO library (bookid, title, author, isbn, bookimage, description) SELECT id, title, author, isbn, bookimage, description FROM __temp__library');
        $this->addSql('DROP TABLE __temp__library');
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, value FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (prodid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value INTEGER NOT NULL)');
        $this->addSql('INSERT INTO product (prodid, name, value) SELECT id, name, value FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__library AS SELECT bookid, title, author, isbn, bookimage, description FROM library');
        $this->addSql('DROP TABLE library');
        $this->addSql('CREATE TABLE library (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, isbn VARCHAR(255) NOT NULL, bookimage VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO library (id, title, author, isbn, bookimage, description) SELECT bookid, title, author, isbn, bookimage, description FROM __temp__library');
        $this->addSql('DROP TABLE __temp__library');
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT prodid, name, value FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value INTEGER NOT NULL)');
        $this->addSql('INSERT INTO product (id, name, value) SELECT prodid, name, value FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }
}
