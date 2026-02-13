<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add contenu_en column rename to article table
 */
final class Version20260213100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename contenuEn column to contenu_en in article table to match Doctrine naming strategy';
    }

    public function up(Schema $schema): void
    {
        // Rename contenuEn to contenu_en in article table
        $this->addSql('ALTER TABLE `article` CHANGE `contenuEn` `contenu_en` LONGTEXT');
    }

    public function down(Schema $schema): void
    {
        // Reverse the rename
        $this->addSql('ALTER TABLE `article` CHANGE `contenu_en` `contenuEn` LONGTEXT');
    }
}
