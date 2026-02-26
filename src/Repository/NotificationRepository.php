<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function markAllReadForUser($user): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->update(Notification::class, 'n')
            ->set('n.isRead', ':true')
            ->where('n.user = :user')
            ->setParameter('true', true)
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->execute();
    }

    public function countUnreadForUser($user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count all unread notifications globally (without filtering by user).
     */
    public function countUnread(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.isRead = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find notifications whose message contains the given term (case‑insensitive).
     *
     * @param string $term
     * @return Notification[]
     */
    public function findByMessageLike(string $term): array
    {
        return $this->createQueryBuilder('n')
            ->where('LOWER(n.message) LIKE :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}