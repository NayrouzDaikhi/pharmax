<?php

namespace App\Command;

use App\Repository\NotificationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:show-notifications',
    description: 'Affiche toutes les notifications créées',
)]
class ShowNotificationsCommand extends Command
{
    public function __construct(
        private NotificationRepository $notificationRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $notifications = $this->notificationRepository->findAll();

        if (empty($notifications)) {
            $io->warning('Aucune notification trouvée');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Total: %d notification(s)', count($notifications)));
        $io->newLine();

        foreach ($notifications as $notification) {
            $io->section(sprintf('Notification #%d - %s', $notification->getId(), $notification->getCreatedAt()->format('Y-m-d H:i:s')));
            $io->writeln('Statut: ' . ($notification->getIsRead() ? '✅ Lue' : '❌ Non lue'));
            $io->newLine();
            $io->block($notification->getMessage(), 'MESSAGE', 'fg=white;bg=blue', '  ');
            $io->newLine();
        }

        return Command::SUCCESS;
    }
}
