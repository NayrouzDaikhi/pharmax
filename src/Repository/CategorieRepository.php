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
     * Rechercher et filtrer les catégories (QueryBuilder pour pagination)
     */
    public function createFilteredQueryBuilder(?string $search = '', ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC')
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($search)) {
            $qb->andWhere('c.nom LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $allowedSortFields = ['c.nom', 'c.createdAt', 'nom', 'createdAt'];
        $sortBy = in_array($sortBy, $allowedSortFields, true) ? $sortBy : 'c.createdAt';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $orderField = str_contains($sortBy, '.') ? $sortBy : 'c.' . $sortBy;
        $qb->orderBy($orderField, $sortOrder);

        return $qb;
    }

    /**
     * Version non paginée (compatibilité éventuelle)
     */
    public function findByFilters(?string $search = '', ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC'): array
    {
        return $this->createFilteredQueryBuilder($search, $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();
    }
}
