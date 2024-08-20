<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820181944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__scores AS SELECT id, user_id_id, score, date FROM scores');
        $this->addSql('DROP TABLE scores');
        $this->addSql('CREATE TABLE scores (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER NOT NULL, score INTEGER NOT NULL, date DATETIME DEFAULT NULL, CONSTRAINT FK_750375E9D86650F FOREIGN KEY (user_id_id) REFERENCES game_player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scores (id, user_id_id, score, date) SELECT id, user_id_id, score, date FROM __temp__scores');
        $this->addSql('DROP TABLE __temp__scores');
        $this->addSql('CREATE INDEX IDX_750375E9D86650F ON scores (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__scores AS SELECT id, user_id_id, score, date FROM scores');
        $this->addSql('DROP TABLE scores');
        $this->addSql('CREATE TABLE scores (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, score INTEGER NOT NULL, date DATETIME DEFAULT NULL, score_id INTEGER NOT NULL, CONSTRAINT FK_750375E9D86650F FOREIGN KEY (user_id_id) REFERENCES game_player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scores (id, user_id_id, score, date) SELECT id, user_id_id, score, date FROM __temp__scores');
        $this->addSql('DROP TABLE __temp__scores');
        $this->addSql('CREATE INDEX IDX_750375E9D86650F ON scores (user_id_id)');
    }
}
