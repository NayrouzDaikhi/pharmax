<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create commandes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE commandes (id INT AUTO_INCREMENT NOT NULL, produits JSON NOT NULL, totales DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, utilisateur_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9D8A7C3B3F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");
        $this->addSql('ALTER TABLE commandes ADD CONSTRAINT FK_9D8A7C3B3F FOREIGN KEY (utilisateur_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commandes DROP FOREIGN KEY FK_9D8A7C3B3F');
        $this->addSql('DROP TABLE commandes');
    }
}
