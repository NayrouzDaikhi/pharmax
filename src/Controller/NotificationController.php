<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications', name: 'notification_')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(NotificationRepository $notificationRepository, ProduitRepository $produitRepository): Response
    {
        $notifications = $notificationRepository->findAll();
        $unreadCount = count(array_filter($notifications, fn($n) => !$n->getIsRead()));

        $dateLimite = new \DateTime('+30 days');
        $expiringProducts = $produitRepository->createQueryBuilder('p')
            ->where('p.dateExpiration <= :date')
            ->setParameter('date', $dateLimite)
            ->orderBy('p.dateExpiration', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'expiringProducts' => $expiringProducts,
        ]);
    }

    #[Route('/{id}/mark-as-read', name: 'mark_as_read', methods: ['POST'])]
    public function markAsRead(int $id, NotificationRepository $notificationRepository, EntityManagerInterface $em): Response
    {
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            throw $this->createNotFoundException('Notification not found');
        }

        $notification->setIsRead(true);
        $em->persist($notification);
        $em->flush();

        $this->addFlash('success', 'Notification marquée comme lue');

        return $this->redirectToRoute('notification_index');
    }
}
