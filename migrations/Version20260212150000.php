<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fresh MySQL Migration: Complete Integrated Schema with All Relationships
 */
final class Version20260212150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Complete integrated MySQL database schema with User relationships';
    }

    public function up(Schema $schema): void
    {
        // User Table - Core authentication and profile
        $this->addSql('CREATE TABLE `user` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(180) NOT NULL UNIQUE,
            `roles` JSON NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `firstName` VARCHAR(255) NOT NULL,
            `lastName` VARCHAR(255),
            `status` VARCHAR(16) DEFAULT "UNBLOCKED",
            `createdAt` DATETIME,
            `updatedAt` DATETIME,
            `googleId` VARCHAR(255),
            `avatar` VARCHAR(255),
            INDEX `idx_email` (`email`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Categorie Table - Product categories
        $this->addSql('CREATE TABLE `categorie` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nom` VARCHAR(255) NOT NULL,
            `description` LONGTEXT NOT NULL,
            `image` VARCHAR(255),
            `createdAt` DATETIME NOT NULL,
            INDEX `idx_nom` (`nom`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Produit Table - Products with category
        $this->addSql('CREATE TABLE `produit` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `categorie_id` INT,
            `nom` VARCHAR(255) NOT NULL,
            `description` LONGTEXT NOT NULL,
            `prix` DOUBLE NOT NULL,
            `image` VARCHAR(255),
            `dateExpiration` DATE NOT NULL,
            `statut` TINYINT(1) NOT NULL,
            `createdAt` DATETIME NOT NULL,
            `quantite` INT NOT NULL,
            FOREIGN KEY (`categorie_id`) REFERENCES `categorie`(`id`) ON DELETE SET NULL,
            INDEX `idx_categorie` (`categorie_id`),
            INDEX `idx_nom` (`nom`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Commandes Table - Orders linked to User
        $this->addSql('CREATE TABLE `commandes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `utilisateur_id` INT,
            `produits` JSON NOT NULL,
            `totales` DOUBLE NOT NULL,
            `statut` VARCHAR(50) DEFAULT "en_attente",
            `created_at` DATETIME NOT NULL,
            FOREIGN KEY (`utilisateur_id`) REFERENCES `user`(`id`) ON DELETE SET NULL,
            INDEX `idx_utilisateur` (`utilisateur_id`),
            INDEX `idx_statut` (`statut`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // LigneCommande Table - Order line items
        $this->addSql('CREATE TABLE `ligne_commandes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `commande_id` INT NOT NULL,
            `nom` VARCHAR(255) NOT NULL,
            `prix` DOUBLE NOT NULL,
            `quantite` INT NOT NULL,
            `sous_total` DOUBLE NOT NULL,
            FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`) ON DELETE CASCADE,
            INDEX `idx_commande` (`commande_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Article Table - Blog/news articles
        $this->addSql('CREATE TABLE `article` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `titre` VARCHAR(255) NOT NULL,
            `contenu` LONGTEXT NOT NULL,
            `contenuEn` LONGTEXT,
            `image` VARCHAR(255),
            `date_creation` DATETIME NOT NULL,
            `date_modification` DATETIME,
            `likes` INT DEFAULT 0,
            INDEX `idx_titre` (`titre`),
            INDEX `idx_date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Commentaire Table - Comments on articles and products with optional User author
        $this->addSql('CREATE TABLE `commentaire` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `article_id` INT,
            `produit_id` INT,
            `user_id` INT,
            `contenu` LONGTEXT NOT NULL,
            `date_publication` DATETIME NOT NULL,
            `statut` VARCHAR(50) DEFAULT "en_attente",
            FOREIGN KEY (`article_id`) REFERENCES `article`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`produit_id`) REFERENCES `produit`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL,
            INDEX `idx_article` (`article_id`),
            INDEX `idx_produit` (`produit_id`),
            INDEX `idx_user` (`user_id`),
            INDEX `idx_statut` (`statut`),
            INDEX `idx_date_publication` (`date_publication`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // CommentaireArchive Table - Archived blocked comments
        $this->addSql('CREATE TABLE `archive_de_commentaire` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `article_id` INT NOT NULL,
            `contenu` LONGTEXT NOT NULL,
            `date_publication` DATETIME NOT NULL,
            `user_name` VARCHAR(255),
            `user_email` VARCHAR(255),
            `reason` VARCHAR(50) DEFAULT "inappropriate",
            `archived_at` DATETIME NOT NULL,
            FOREIGN KEY (`article_id`) REFERENCES `article`(`id`) ON DELETE CASCADE,
            INDEX `idx_article` (`article_id`),
            INDEX `idx_archived_at` (`archived_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Reclamation Table - Customer complaints linked to User
        $this->addSql('CREATE TABLE `reclamation` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT,
            `titre` VARCHAR(255) NOT NULL,
            `description` LONGTEXT NOT NULL,
            `dateCreation` DATETIME NOT NULL,
            `statut` VARCHAR(50) DEFAULT "En attente",
            FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL,
            INDEX `idx_user` (`user_id`),
            INDEX `idx_statut` (`statut`),
            INDEX `idx_dateCreation` (`dateCreation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Reponse Table - Complaint replies
        $this->addSql('CREATE TABLE `reponse` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `reclamation_id` INT NOT NULL,
            `contenu` LONGTEXT NOT NULL,
            `dateReponse` DATETIME NOT NULL,
            FOREIGN KEY (`reclamation_id`) REFERENCES `reclamation`(`id`) ON DELETE CASCADE,
            INDEX `idx_reclamation` (`reclamation_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Notification Table - User notifications
        $this->addSql('CREATE TABLE `notification` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `message` LONGTEXT NOT NULL,
            `createdAt` DATETIME NOT NULL,
            `isRead` TINYINT(1) DEFAULT 0,
            FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
            INDEX `idx_user` (`user_id`),
            INDEX `idx_isRead` (`isRead`),
            INDEX `idx_createdAt` (`createdAt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ResetPasswordRequest Table - Password recovery tokens
        $this->addSql('CREATE TABLE `reset_password_request` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `selector` VARCHAR(20) NOT NULL,
            `hashedToken` VARCHAR(100) NOT NULL,
            `requestedAt` DATETIME NOT NULL,
            `expiresAt` DATETIME NOT NULL,
            FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
            UNIQUE INDEX `UNIQ_7CE748A1E7927C74` (`user_id`),
            INDEX `idx_selector` (`selector`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        // Drop all tables in reverse order of dependencies
        $this->addSql('DROP TABLE IF EXISTS `reset_password_request`');
        $this->addSql('DROP TABLE IF EXISTS `notification`');
        $this->addSql('DROP TABLE IF EXISTS `reponse`');
        $this->addSql('DROP TABLE IF EXISTS `reclamation`');
        $this->addSql('DROP TABLE IF EXISTS `archive_de_commentaire`');
        $this->addSql('DROP TABLE IF EXISTS `commentaire`');
        $this->addSql('DROP TABLE IF EXISTS `ligne_commandes`');
        $this->addSql('DROP TABLE IF EXISTS `commandes`');
        $this->addSql('DROP TABLE IF EXISTS `article`');
        $this->addSql('DROP TABLE IF EXISTS `produit`');
        $this->addSql('DROP TABLE IF EXISTS `categorie`');
        $this->addSql('DROP TABLE IF EXISTS `user`');
    }
}
