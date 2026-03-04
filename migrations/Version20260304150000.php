<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add dataFaceApi column to user table for Face API integration.
 */
final class Version20260304150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dataFaceApi TEXT column to user table for Face API integration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD data_face_api LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN data_face_api');
    }
}
