<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211134120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD COLUMN contenu_en CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__article AS SELECT id, titre, contenu, image, date_creation, date_modification, likes FROM article');
        $this->addSql('DROP TABLE article');
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu CLOB NOT NULL, image VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, likes INTEGER NOT NULL)');
        $this->addSql('INSERT INTO article (id, titre, contenu, image, date_creation, date_modification, likes) SELECT id, titre, contenu, image, date_creation, date_modification, likes FROM __temp__article');
        $this->addSql('DROP TABLE __temp__article');
    }
}
