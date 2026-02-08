<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208203100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archive_de_commentaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, contenu CLOB NOT NULL, date_publication DATETIME NOT NULL, user_name VARCHAR(255) DEFAULT NULL, user_email VARCHAR(255) DEFAULT NULL, reason VARCHAR(50) NOT NULL, archived_at DATETIME NOT NULL, article_id INTEGER NOT NULL, CONSTRAINT FK_522D8E587294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_522D8E587294869C ON archive_de_commentaire (article_id)');
        $this->addSql('DROP TABLE commentaire_archive');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire_archive (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, contenu CLOB NOT NULL COLLATE "BINARY", date_publication DATETIME NOT NULL, user_name VARCHAR(255) DEFAULT NULL COLLATE "BINARY", user_email VARCHAR(255) DEFAULT NULL COLLATE "BINARY", reason VARCHAR(50) NOT NULL COLLATE "BINARY", archived_at DATETIME NOT NULL, article_id INTEGER NOT NULL, CONSTRAINT FK_A634CFCF7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A634CFCF7294869C ON commentaire_archive (article_id)');
        $this->addSql('DROP TABLE archive_de_commentaire');
    }
}
