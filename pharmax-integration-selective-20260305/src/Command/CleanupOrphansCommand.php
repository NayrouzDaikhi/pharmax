<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:cleanup-orphans',
    description: 'Remove orphaned foreign key references',
)]
class CleanupOrphansCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();
        
        // Mettre à NULL les categorie_id qui n'existent pas
        $sql = "UPDATE produit SET categorie_id = NULL 
                WHERE categorie_id NOT IN (SELECT id FROM categorie) 
                AND categorie_id IS NOT NULL";
        
        $connection->executeStatement($sql);
        
        $output->writeln('<info>Orphaned foreign keys cleaned successfully!</info>');
        return Command::SUCCESS;
    }
}
