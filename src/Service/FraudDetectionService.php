<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\User;
use App\Repository\CommandeRepository;

class FraudDetectionService
{
    public function __construct(
        private CommandeRepository $commandeRepository,
    ) {}

    /**
     * Calcule un score de risque (0–100) pour une commande.
     */
    public function calculateRisk(Commande $commande): int
    {
        $risk = 0;

        $user = $commande->getUtilisateur();
        if ($user instanceof User) {
            $ordersToday = $this->commandeRepository->countUserOrdersToday($user);
            // 3 commandes ou plus dans la journée = gros signal
            if ($ordersToday >= 3) {
                $risk += 70;
            } elseif ($ordersToday === 2) {
                $risk += 30;
            }
        }

        $total = (float) $commande->getTotales();
        if ($total > 50) {
            $risk += 20;
        }
        if ($total > 100) {
            $risk += 60;
        }

        $maxQty = 0;
        foreach ($commande->getLignes() as $ligne) {
            $q = (int) $ligne->getQuantite();
            if ($q > $maxQty) {
                $maxQty = $q;
            }
        }
        if ($maxQty >= 3) {
            // trois articles identiques ou plus : signe suspect
            $risk += 20;
        } elseif ($maxQty === 2) {
            // deux exemplaires d'un même produit
            $risk += 10;
        }

        return max(0, min(100, $risk));
    }
}

