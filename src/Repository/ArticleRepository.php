<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Find articles with search functionality
     */
    public function findBySearch(?string $search = null, int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a');
        
        if (!empty($search)) {
            $search = '%' . addcslashes($search, '%_\\') . '%';
            $qb->where('a.titre LIKE :search')
               ->orWhere('a.contenu LIKE :search')
               ->orWhere('a.contenu_en LIKE :search')
               ->setParameter('search', $search);
        }
        
        $qb->orderBy('a.created_at', 'DESC')
           ->setFirstResult($offset);
        
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Count articles with search
     */
    public function countBySearch(?string $search = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)');
        
        if (!empty($search)) {
            $search = '%' . addcslashes($search, '%_\\') . '%';
            $qb->where('a.titre LIKE :search')
               ->orWhere('a.contenu LIKE :search')
               ->orWhere('a.contenu_en LIKE :search')
               ->setParameter('search', $search);
        }
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find recent articles
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all articles ordered by creation date
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
