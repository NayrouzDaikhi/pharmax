<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\AdminEmailDigestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'admin:send-digest',
    description: 'Send daily or weekly digest to admins',
)]
class SendAdminDigestCommand extends Command
{
    public function __construct(
        private AdminEmailDigestService $digestService,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of digest (daily/weekly)', 'daily')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Specific admin email to send to')
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'Date for the digest (Y-m-d format)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type') ?? 'daily';
        $email = $input->getOption('email');
        $dateStr = $input->getOption('date');

        $date = null;
        if ($dateStr) {
            $date = \DateTime::createFromFormat('Y-m-d', $dateStr);
            if (!$date) {
                $output->writeln('<error>Invalid date format. Use Y-m-d</error>');
                return Command::FAILURE;
            }
        }

        // Get admins by searching for users with ROLE_ADMIN
        $admins = $this->userRepository->searchUsers(['role' => 'ROLE_ADMIN']);

        if (empty($admins)) {
            $output->writeln('<warning>No admin users found</warning>');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($admins as $admin) {
            if ($email && $admin->getEmail() !== $email) {
                continue;
            }

            try {
                if ($type === 'weekly') {
                    $result = $this->digestService->sendWeeklyDigest($admin->getEmail(), $date);
                } else {
                    $result = $this->digestService->sendDailyDigest($admin->getEmail(), $date);
                }

                if ($result) {
                    $output->writeln("<info>Sent {$type} digest to {$admin->getEmail()}</info>");
                    $count++;
                } else {
                    $output->writeln("<error>Failed to send digest to {$admin->getEmail()}</error>");
                }
            } catch (\Exception $e) {
                $output->writeln("<error>Error sending digest to {$admin->getEmail()}: {$e->getMessage()}</error>");
            }
        }

        $output->writeln("<info>Digest emails sent: {$count}</info>");
        return Command::SUCCESS;
    }
}
