<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function createFilteredQueryBuilder(?string $search = '', ?string $categorie = '', ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC')
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c', 'WITH', 'c.id IS NOT NULL')
            ->select('p', 'c');

        if (!empty($search)) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($categorie) && $categorie !== '0') {
            $qb->andWhere('c.id = :categorie')
                ->setParameter('categorie', $categorie);
        }

        $allowedSortFields = ['p.nom', 'p.prix', 'p.createdAt', 'p.dateExpiration', 'p.quantite', 'nom', 'prix', 'createdAt', 'dateExpiration', 'quantite'];
        $sortBy = in_array($sortBy, $allowedSortFields, true) ? $sortBy : 'p.createdAt';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $orderField = str_contains($sortBy, '.') ? $sortBy : 'p.' . $sortBy;
        $qb->orderBy($orderField, $sortOrder);

        return $qb;
    }

    public function findByFilters(?string $search = '', ?string $categorie = '', ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC'): array
    {
        return $this->createFilteredQueryBuilder($search, $categorie, $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter le nombre total de produits
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les produits expirés
     */
    public function countExpired(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.dateExpiration < :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Produits dont la date d'expiration est aujourd'hui
     */
    public function findExpiringToday(): array
    {
        $todayStart = (new \DateTime())->setTime(0, 0, 0);
        $todayEnd = (clone $todayStart)->modify('+1 day');

        return $this->createQueryBuilder('p')
            ->where('p.dateExpiration >= :todayStart')
            ->andWhere('p.dateExpiration < :todayEnd')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les produits valables (en stock)
     */
    public function countAvailable(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.statut = :valable')
            ->andWhere('p.dateExpiration >= :today')
            ->setParameter('valable', true)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les produits hors stock
     */
    public function countOutOfStock(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.statut = :rupture')
            ->setParameter('rupture', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtenir les statistiques de produits valables vs hors stock par mois
     */
    public function getStatusByMonth(): array
    {
        // Récupérer tous les produits avec leur date de création et statut
        $produits = $this->createQueryBuilder('p')
            ->select('p.createdAt, p.statut')
            ->getQuery()
            ->getResult();

        // Grouper par mois en PHP
        $monthsData = [];
        foreach ($produits as $produit) {
            $monthYear = $produit['createdAt']->format('Y-m');
            
            if (!isset($monthsData[$monthYear])) {
                $monthsData[$monthYear] = [
                    'month' => $monthYear,
                    'valable' => 0,
                    'hors_stock' => 0
                ];
            }
            
            if ($produit['statut']) {
                $monthsData[$monthYear]['valable']++;
            } else {
                $monthsData[$monthYear]['hors_stock']++;
            }
        }

        // Trier par mois décroissant
        krsort($monthsData);
        return array_values($monthsData);
    }

    /**
     * Obtenir les produits les plus chers (Top 5)
     */
    public function getMostExpensiveProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.nom, p.prix')
            ->orderBy('p.prix', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtenir les produits les moins chers (Top 5)
     */
    public function getLeastExpensiveProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.nom, p.prix')
            ->orderBy('p.prix', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
