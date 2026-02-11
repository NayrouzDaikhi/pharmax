<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211110922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL, is_read BOOLEAN NOT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, nom, description, prix, image, date_expiration, statut, created_at, categorie_id, quantite FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description CLOB NOT NULL, prix DOUBLE PRECISION NOT NULL, image VARCHAR(255) DEFAULT NULL, date_expiration DATE NOT NULL, statut BOOLEAN NOT NULL, created_at DATETIME NOT NULL, categorie_id INTEGER DEFAULT NULL, quantite INTEGER NOT NULL, CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, nom, description, prix, image, date_expiration, statut, created_at, categorie_id, quantite) SELECT id, nom, description, prix, image, date_expiration, statut, created_at, categorie_id, quantite FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notification');
        $this->addSql('CREATE TEMPORARY TABLE __temp__produit AS SELECT id, nom, description, prix, image, date_expiration, statut, created_at, quantite, categorie_id FROM produit');
        $this->addSql('DROP TABLE produit');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description CLOB NOT NULL, prix DOUBLE PRECISION NOT NULL, image VARCHAR(255) DEFAULT NULL, date_expiration DATE NOT NULL, statut BOOLEAN NOT NULL, created_at DATETIME NOT NULL, quantite INTEGER DEFAULT 0 NOT NULL, categorie_id INTEGER DEFAULT NULL, CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO produit (id, nom, description, prix, image, date_expiration, statut, created_at, quantite, categorie_id) SELECT id, nom, description, prix, image, date_expiration, statut, created_at, quantite, categorie_id FROM __temp__produit');
        $this->addSql('DROP TABLE __temp__produit');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
    }
}
