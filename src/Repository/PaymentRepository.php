<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Find successful payments
     */
    public function findSuccessful(): array
    {
        return $this->findBy(['statut' => 'succeeded']);
    }

    /**
     * Find failed payments
     */
    public function findFailed(): array
    {
        return $this->findBy(['statut' => 'failed']);
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as totalPayments, SUM(p.montant) as totalAmount')
            ->where('p.datePaiement >= :from')
            ->andWhere('p.datePaiement < :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $result = $qb->getQuery()->getOneOrNullResult();

        return [
            'totalPayments' => $result['totalPayments'] ?? 0,
            'totalAmount' => $result['totalAmount'] ?? 0,
        ];
    }

    /**
     * Get payment method breakdown
     */
    public function getPaymentMethodBreakdown(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.methodePaiement, COUNT(p.id) as count, SUM(p.montant) as total')
            ->groupBy('p.methodePaiement')
            ->getQuery()
            ->getResult();
    }
}
