<?php

namespace App\Command;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:test:email',
    description: 'Envoie un email de test pour une commande',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private CommandeRepository $commandeRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('commande_id', InputArgument::REQUIRED, 'ID de la commande à tester')
            ->setHelp('Envoie un email de confirmation pour une commande existante');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $commandeId = $input->getArgument('commande_id');

        // Récupère la commande
        $commande = $this->commandeRepository->find($commandeId);

        if (!$commande) {
            $io->error("Commande #$commandeId non trouvée");
            return Command::FAILURE;
        }

        $io->info("Envoi de l'email de confirmation pour Commande #" . $commande->getId());

        try {
            $email = (new TemplatedEmail())
                ->from('orders@pharmax.example')
                ->to($commande->getUtilisateur()?->getEmail() ?? 'test@example.com')
                ->subject('Confirmation de votre commande Pharmax #' . $commande->getId())
                ->htmlTemplate('emails/commande_confirmation.html.twig')
                ->context([
                    'commande' => $commande,
                ]);

            $this->mailer->send($email);

            $io->success('✓ Email envoyé avec succès!');
            $io->note('Destinataire : ' . ($commande->getUtilisateur()?->getEmail() ?? 'test@example.com'));
            $io->note('Commande : #' . $commande->getId() . ' (' . number_format($commande->getTotales(), 2) . ' TND)');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
