<?php

namespace App\Command;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reclamation:delete-duplicates',
    description: 'Supprime les réclamations en double (même titre et description), en conservant la plus ancienne.',
)]
class ReclamationDeleteDuplicatesCommand extends Command
{
    private $reclamationRepository;
    private $entityManager;

    public function __construct(ReclamationRepository $reclamationRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->reclamationRepository = $reclamationRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        // Pas besoin d'arguments ou d'options pour cette commande simple
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Suppression des réclamations en double');

        $io->info('Recherche des réclamations en double...');
        $reclamationsToDelete = $this->reclamationRepository->findDuplicateReclamationsToDelete();

        if (empty($reclamationsToDelete)) {
            $io->success('Aucune réclamation en double trouvée. La base de données est propre !');
            return Command::SUCCESS;
        }

        $io->warning(sprintf('%d réclamation(s) en double trouvée(s) à supprimer.', count($reclamationsToDelete)));

        foreach ($reclamationsToDelete as $reclamation) {
            $this->entityManager->remove($reclamation);
            $io->text(sprintf('Suppression de la réclamation ID: %d, Titre: "%s"', $reclamation->getId(), $reclamation->getTitre()));
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d réclamation(s) en double supprimée(s) avec succès.', count($reclamationsToDelete)));

        return Command::SUCCESS;
    }
}
