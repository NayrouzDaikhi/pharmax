<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\ExpirationNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationWidgetController extends AbstractController
{
    private ExpirationNotificationService $expirationService;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(ExpirationNotificationService $expirationService, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->expirationService = $expirationService;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function widget(NotificationRepository $notificationRepository): Response
    {
        // generate expiration notifications upfront (duplicates are filtered)
        $this->expirationService->createDbNotificationsForExpiringProducts(
            $this->em,
            $this->userRepository,
            $notificationRepository,
            7
        );

        // produits qui expirent dans les prochains jours
        $expiringProducts = $this->expirationService->getExpiringProducts(7);
        $user = $this->getUser();
        if ($user) {
            $notifications = $notificationRepository->findBy(['user' => $user], ['createdAt' => 'DESC'], 8);
        } else {
            // Fallback: show latest notifications globally if no user in the subrequest
            $notifications = $notificationRepository->findBy([], ['createdAt' => 'DESC'], 8);
        }

        return $this->render('_partials/notification_icon.html.twig', [
            'notifications' => $notifications,
            'expiringProducts' => $expiringProducts,
        ]);
    }
}