<?php

namespace App\Service;

use App\Entity\Reclamation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendReclamationConfirmationEmail(Reclamation $reclamation, string $recipientEmail): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@pharmax.com') // Remplacez par votre adresse e-mail d'expéditeur
            ->to($recipientEmail)
            ->subject('Confirmation de votre réclamation #' . $reclamation->getId())
            ->htmlTemplate('emails/reclamation_confirmation.html.twig')
            ->context([
                'reclamation' => $reclamation,
            ]);

        $this->mailer->send($email);
    }
}
