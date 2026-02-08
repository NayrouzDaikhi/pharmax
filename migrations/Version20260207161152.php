<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207161152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu CLOB NOT NULL, image VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE commentaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, contenu CLOB NOT NULL, date_publication DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, article_id INTEGER NOT NULL, FOREIGN KEY (article_id) REFERENCES article (id))');
        $this->addSql('CREATE INDEX IDX_67F068BC7294869C ON commentaire (article_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
