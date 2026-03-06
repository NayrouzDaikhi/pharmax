<?php

namespace App\Command;

use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use App\Repository\NotificationRepository;
use App\Service\ExpirationNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-expiration',
    description: 'Check for products expiring soon and send notifications',
)]
class CheckExpirationCommand extends Command
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
        private ExpirationNotificationService $expirationService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Number of days to check ahead (default: 7)',
            7
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getOption('days');

        $io->info(sprintf('Checking for products expiring in the next %d days...', $days));

        // Get expiring products with eager-loaded category
        $produits = $this->produitRepository->findExpiringProductsWithCategory($days);

        if (empty($produits)) {
            $io->success(sprintf('No products expiring in the next %d days.', $days));
            return Command::SUCCESS;
        }

        $io->info(sprintf('Processing %d product(s)...', count($produits)));

        // Create database notifications using service (avoids duplicates)
        $created = $this->expirationService->createDbNotificationsForExpiringProducts(
            $this->em,
            $this->userRepository,
            $this->notificationRepository,
            $days
        );

        // Optionally send emails
        if ($created > 0 && $input->getOption('send-emails') !== false) {
            $io->writeln('<fg=blue>Sending email notifications...</>');
            try {
                $this->expirationService->sendEmailNotification($produits);
                $io->writeln('<fg=green>✓ Emails sent successfully</>');
            } catch (\Exception $e) {
                $io->writeln(sprintf('<fg=red>✗ Error sending emails: %s</>', $e->getMessage()));
            }
        }

        $io->success(sprintf('%d notification(s) created successfully!', $created));

        return Command::SUCCESS;
    }
}
