<?php

namespace App\Command;

use App\Repository\ProduitRepository;
use App\Service\GeminiService;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-expiration',
    description: 'Check for products expiring soon and generate notifications',
)]
class CheckExpirationCommand extends Command
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private GeminiService $geminiService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // Pas d'arguments ou d'options supplémentaires nécessaires
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Vérification des produits arrivant à expiration...');

        $dateLimite = new \DateTime('+30 days');

        $produits = $this->produitRepository->createQueryBuilder('p')
            ->where('p.dateExpiration <= :date')
            ->setParameter('date', $dateLimite)
            ->getQuery()
            ->getResult();

        if (empty($produits)) {
            $io->success('Aucun produit arrivant à expiration dans les 30 prochains jours.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Traitement de %d produit(s)...', count($produits)));

        foreach ($produits as $produit) {
            $io->writeln(sprintf('  - Traitement du produit : %s', $produit->getNom()));

            $message = $this->geminiService->generateExpirationMessage(
                $produit->getNom(),
                30
            );

            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setCreatedAt(new \DateTime());
            $notification->setIsRead(false);

            $this->em->persist($notification);
        }

        $this->em->flush();

        $io->success(sprintf('%d notification(s) créée(s) avec succès!', count($produits)));

        return Command::SUCCESS;
    }
}
