<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add promotion_pourcentage to produit table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit ADD promotion_pourcentage INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit DROP promotion_pourcentage');
    }
}
