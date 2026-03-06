<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Uncomment if you add Twilio support later
// use Twilio\Rest\Client;

class ExpirationNotificationService
{
    private ProduitRepository $produitRepository;
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(ProduitRepository $produitRepository, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->produitRepository = $produitRepository;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Create DB notifications for expiring products for all users (or a subset).
     * Avoids creating duplicate notifications with identical message for the same user.
     *
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param NotificationRepository $notificationRepository
     * @param int $daysBefore
     * @return int number of notifications created
     */
    public function createDbNotificationsForExpiringProducts(EntityManagerInterface $em, UserRepository $userRepository, NotificationRepository $notificationRepository, int $daysBefore = 7): int
    {
        $produits = $this->getExpiringProducts($daysBefore);
        if (empty($produits)) {
            return 0;
        }

        $users = $userRepository->findAll();
        $created = 0;

        foreach ($produits as $p) {
            // build link to edit page
            $editUrl = $this->urlGenerator->generate('app_produit_edit', ['id' => $p->getId()]);
            $date = $p->getDateExpiration() ? $p->getDateExpiration()->format('d/m/Y') : '—';
            // construct message without backslashes; avoid quoting issues by concatenating
            $message = 'Le produit "<a href="' . $editUrl . '">'
                     . htmlspecialchars($p->getNom(), ENT_QUOTES, 'UTF-8')
                     . '</a>" expire le ' . $date;
            // if any legacy notifications contain stray backslashes, clean them here as well
            $message = str_replace('\\"', '"', $message);

            foreach ($users as $user) {
                // avoid duplicate message for same user
                $exists = $notificationRepository->findOneBy(['message' => $message, 'user' => $user]);
                if ($exists) {
                    continue;
                }

                $notif = new Notification();
                $notif->setMessage($message);
                $notif->setCreatedAt(new \DateTime());
                $notif->setIsRead(false);
                $notif->setUser($user);

                $em->persist($notif);
                $created++;
            }
        }

        if ($created > 0) {
            $em->flush();
        }

        return $created;
    }

    /**
     * Retourne les produits dont la date d'expiration se situe dans les X prochains jours.
     *
     * @param int $daysBefore nombre de jours avant expiration (7 par défaut)
     * @return array<int, \App\Entity\Produit>
     */
    public function getExpiringProducts(int $daysBefore = 7): array
    {
        $today = new \DateTime();
        $endDate = (clone $today)->modify("+$daysBefore days");

        return $this->produitRepository->createQueryBuilder('p')
            ->where('p.dateExpiration BETWEEN :today AND :endDate')
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Envoie un email pour chaque produit proche de l'expiration.
     *
     * @param array<int, \App\Entity\Produit> $produits
     */
    public function sendEmailNotification(array $produits): void
    {
        foreach ($produits as $produit) {
            $email = (new Email())
                ->from('no-reply@myshop.com')
                ->to('admin@myshop.com') // modifier selon le destinataire réel
                ->subject('Produit bientôt expiré : '.$produit->getNom())
                ->text("Le produit ".$produit->getNom()." expire le ".$produit->getDateExpiration()->format('d-m-Y'));

            $this->mailer->send($email);
        }
    }

    /**
     * Exemple d'ajout d'envoi SMS via Twilio (optionnel).
     * à condition d'installer twilio/sdk et de configurer les identifiants.
     *
     * @param array<int, \App\Entity\Produit> $produits
     */
    public function sendSMSNotification(array $produits): void
    {
        // require twilio/sdk via composer
        // use Twilio\Rest\Client;

        $sid = 'TON_TWILIO_SID';
        $token = 'TON_TWILIO_TOKEN';
        $from = '+123456789'; // numéro Twilio
        $to = '+216XXXXXXXX'; // numéro destinataire

        $client = new \Twilio\Rest\Client($sid, $token);

        foreach ($produits as $p) {
            $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => "Produit ".$p->getNom()." expire le ".$p->getDateExpiration()->format('d-m-Y')
                ]
            );
        }
    }

    // plus tard on pourra également implémenter l'envoi de notifications WebPush
}