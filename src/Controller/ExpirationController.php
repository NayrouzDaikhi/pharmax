<?php

namespace App\Controller;

use App\Service\ExpirationNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExpirationController extends AbstractController
{
    #[Route('/api/notify-expiration', name: 'api_notify_expiration', methods: ['GET'])]
    public function notify(ExpirationNotificationService $service): JsonResponse
    {
        $produits = $service->getExpiringProducts();

        // envoyer un email à l'admin (ou autre destinataire configuré)
        $service->sendEmailNotification($produits);

        // on pourrait aussi appeler sendSMSNotification() si configuré

        // préparer la réponse JSON
        $data = [];
        foreach ($produits as $p) {
            $date = $p->getDateExpiration()?->format('Y-m-d');
            $data[] = [
                'id' => $p->getId(),
                'nom' => $p->getNom(),
                'dateExpiration' => $date,
                // alias court pour compatibilité
                'dateExpire' => $date,
            ];
        }

        return new JsonResponse([
            'status' => 'success',
            'produits' => $data,
        ]);
    }
}