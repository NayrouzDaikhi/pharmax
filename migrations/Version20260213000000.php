<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix camelCase to snake_case column names to match Doctrine naming strategy
 */
final class Version20260213000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename camelCase columns to snake_case to match Doctrine underscore_number_aware naming strategy';
    }

    public function up(Schema $schema): void
    {
        // Rename columns in user table to match snake_case naming strategy
        $this->addSql('ALTER TABLE `user` CHANGE `firstName` `first_name` VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE `lastName` `last_name` VARCHAR(255)');
        $this->addSql('ALTER TABLE `user` CHANGE `status` `status` VARCHAR(16) DEFAULT "UNBLOCKED"');
        $this->addSql('ALTER TABLE `user` CHANGE `createdAt` `created_at` DATETIME');
        $this->addSql('ALTER TABLE `user` CHANGE `updatedAt` `updated_at` DATETIME');
        $this->addSql('ALTER TABLE `user` CHANGE `googleId` `google_id` VARCHAR(255)');

        // Rename columns in categorie table
        $this->addSql('ALTER TABLE `categorie` CHANGE `createdAt` `created_at` DATETIME NOT NULL');

        // Rename columns in produit table
        $this->addSql('ALTER TABLE `produit` CHANGE `categorie_id` `categorie_id` INT');
        $this->addSql('ALTER TABLE `produit` CHANGE `dateExpiration` `date_expiration` DATE NOT NULL');
        $this->addSql('ALTER TABLE `produit` CHANGE `createdAt` `created_at` DATETIME NOT NULL');

        // Rename columns in reclamation table
        $this->addSql('ALTER TABLE `reclamation` CHANGE `dateCreation` `date_creation` DATETIME NOT NULL');

        // Rename columns in reponse table
        $this->addSql('ALTER TABLE `reponse` CHANGE `dateReponse` `date_reponse` DATETIME NOT NULL');

        // Rename columns in notification table
        $this->addSql('ALTER TABLE `notification` CHANGE `createdAt` `created_at` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `notification` CHANGE `isRead` `is_read` TINYINT(1) DEFAULT 0');

        // Rename columns in reset_password_request table
        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `hashedToken` `hashed_token` VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `requestedAt` `requested_at` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `expiresAt` `expires_at` DATETIME NOT NULL');

        // Rename columns in article table
        $this->addSql('ALTER TABLE `article` CHANGE `date_creation` `created_at` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `article` CHANGE `date_modification` `updated_at` DATETIME');

        // Rename columns in commentaire table
        $this->addSql('ALTER TABLE `commentaire` CHANGE `date_publication` `created_at` DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Reverse the renames
        $this->addSql('ALTER TABLE `user` CHANGE `first_name` `firstName` VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE `last_name` `lastName` VARCHAR(255)');
        $this->addSql('ALTER TABLE `user` CHANGE `created_at` `createdAt` DATETIME');
        $this->addSql('ALTER TABLE `user` CHANGE `updated_at` `updatedAt` DATETIME');
        $this->addSql('ALTER TABLE `user` CHANGE `google_id` `googleId` VARCHAR(255)');

        $this->addSql('ALTER TABLE `categorie` CHANGE `created_at` `createdAt` DATETIME NOT NULL');

        $this->addSql('ALTER TABLE `produit` CHANGE `date_expiration` `dateExpiration` DATE NOT NULL');
        $this->addSql('ALTER TABLE `produit` CHANGE `created_at` `createdAt` DATETIME NOT NULL');

        $this->addSql('ALTER TABLE `reclamation` CHANGE `date_creation` `dateCreation` DATETIME NOT NULL');

        $this->addSql('ALTER TABLE `reponse` CHANGE `date_reponse` `dateReponse` DATETIME NOT NULL');

        $this->addSql('ALTER TABLE `notification` CHANGE `created_at` `createdAt` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `notification` CHANGE `is_read` `isRead` TINYINT(1) DEFAULT 0');

        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `hashed_token` `hashedToken` VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `requested_at` `requestedAt` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `reset_password_request` CHANGE `expires_at` `expiresAt` DATETIME NOT NULL');

        $this->addSql('ALTER TABLE `article` CHANGE `created_at` `date_creation` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `article` CHANGE `updated_at` `date_modification` DATETIME');

        $this->addSql('ALTER TABLE `commentaire` CHANGE `created_at` `date_publication` DATETIME NOT NULL');
    }
}
