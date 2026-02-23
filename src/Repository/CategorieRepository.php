<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Compter le nombre total de catégories
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Rechercher et filtrer les catégories
     */
    public function findByFilters(?string $search = '', ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('c');

        // Recherche par nom ou description
        if (!empty($search)) {
            $qb->andWhere('c.nom LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Tri
        $allowedSortFields = ['nom', 'createdAt'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'createdAt';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('c.' . $sortBy, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
