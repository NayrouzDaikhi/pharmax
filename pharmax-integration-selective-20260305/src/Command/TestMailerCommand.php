<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-mail',
    description: 'Send a test email to verify mailer configuration'
)]
class TestMailerCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing email sending...');

        try {
            $email = (new Email())
                ->from('agrivisionconnectinc@gmail.com')
                ->to('mquny.gaming@gmail.com')
                ->subject('Test Email from Symfony')
                ->text('This is a test email from Symfony mailer. If you see this, the system is working!');

            $this->mailer->send($email);
            $output->writeln('<info>✓ Email sent successfully!</info>');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>✗ Error sending email:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('<error>' . $e->getTraceAsString() . '</error>');
            return Command::FAILURE;
        }
    }
}
