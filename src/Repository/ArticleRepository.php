<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

<<<<<<< HEAD
    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
=======
    /**
     * Chercher les articles par mots-clés
     */
    public function searchByKeywords(array $keywords, int $limit = 5): array
    {
        if (empty($keywords)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a');
        
        // Créer les conditions OR pour chaque mot-clé
        $orConditions = [];
        foreach ($keywords as $index => $keyword) {
            $param = 'keyword_' . $index;
            $orConditions[] = $qb->expr()->orX(
                $qb->expr()->like('a.titre', ':' . $param),
                $qb->expr()->like('a.contenu', ':' . $param),
                $qb->expr()->like('a.contenuEn', ':' . $param)
            );
            $qb->setParameter($param, '%' . $keyword . '%');
        }

        if (!empty($orConditions)) {
            $qb->where($qb->expr()->orX(...$orConditions));
        }

        return $qb->orderBy('a.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
>>>>>>> gestion-article
}
