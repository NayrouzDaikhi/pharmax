<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;

class ProductRecommender
{
    public function __construct(
        private CommandeRepository $commandeRepository,
        private ProduitRepository $produitRepository,
    ) {}

    /**
     * Retourne les produits les plus souvent commandés par un utilisateur.
     *
     * @return array<\App\Entity\Produit>
     */
    public function getRecommendationsForUser(User $user, int $limit = 3): array
    {
        $commandes = $this->commandeRepository->findByUtilisateur($user);

        if (empty($commandes)) {
            return [];
        }

        $counts = [];

        foreach ($commandes as $commande) {
            foreach ($commande->getProduits() as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = $item['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $qty = (int) ($item['quantite'] ?? 1);
                $counts[$id] = ($counts[$id] ?? 0) + max(1, $qty);
            }
        }

        if (empty($counts)) {
            return [];
        }

        arsort($counts);
        $topIds = array_slice(array_keys($counts), 0, $limit);

        if (empty($topIds)) {
            return [];
        }

        // Récupérer les produits encore valides, en conservant l'ordre des IDs
        $qb = $this->produitRepository->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $topIds);

        // Optionnel : seulement les produits en stock et non expirés
        $qb->andWhere('p.statut = :statut')
            ->setParameter('statut', true);

        $produits = $qb->getQuery()->getResult();

        // Réordonner selon la popularité
        $byId = [];
        foreach ($produits as $p) {
            $byId[$p->getId()] = $p;
        }

        $ordered = [];
        foreach ($topIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }
}

