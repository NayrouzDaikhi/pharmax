<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findByUtilisateur($utilisateur)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByStatut($statut)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByIdOrStatut(?int $id = null, ?string $statut = null)
    {
        $qb = $this->createQueryBuilder('c');

        if ($id !== null) {
            $qb->andWhere('c.id = :id')
               ->setParameter('id', $id);
        }

        if ($statut !== null && $statut !== '') {
            $qb->andWhere('c.statut = :statut')
               ->setParameter('statut', $statut);
        }

        $qb->orderBy('c.created_at', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findByDateRange(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null)
    {
        $qb = $this->createQueryBuilder('c');

        if ($start !== null) {
            $qb->andWhere('c.created_at >= :start')
               ->setParameter('start', $start);
        }

        if ($end !== null) {
            $qb->andWhere('c.created_at <= :end')
               ->setParameter('end', $end);
        }

        $qb->orderBy('c.created_at', 'DESC');

        return $qb->getQuery()->getResult();
    }


    public function findRecentCommandes($limit = 10)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByStatut($statut)
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getStatistics()
    {
        return [
            'en_cours' => $this->countByStatut('en_cours'),
            'en_attente' => $this->countByStatut('en_attente'),
            'payee' => $this->countByStatut('payee'),
            'annule' => $this->countByStatut('annule'),
            'total' => $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }
}
