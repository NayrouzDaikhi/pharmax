<?php

namespace App\Repository;

use App\Entity\CommentaireArchive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentaireArchive>
 *
 * @method CommentaireArchive|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentaireArchive|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentaireArchive[]    findAll()
 * @method CommentaireArchive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaireArchive::class);
    }

    public function save(CommentaireArchive $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommentaireArchive $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all archived comments with left join on articles to avoid EntityNotFoundException
     */
    public function findAllWithArticles()
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.article', 'article')
            ->addSelect('article')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
