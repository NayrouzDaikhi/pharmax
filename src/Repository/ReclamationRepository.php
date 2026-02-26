<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 *
 * @method Reclamation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamation[]    findAll()
 * @method Reclamation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function add(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Search reclamations by user and query
     */
    public function searchByUserAndQuery($user, ?string $query): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.reponses', 'rep')
            ->addSelect('rep')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user);

        // Get IDs of duplicate reclamations to exclude
        $duplicateIdsToExclude = $this->getDuplicateReclamationIdsToExclude();

        if (!empty($duplicateIdsToExclude)) {
            $qb->andWhere('r.id NOT IN (:duplicateIds)')
               ->setParameter('duplicateIds', $duplicateIdsToExclude);
        }

        if ($query) {
            $qb->andWhere('r.titre LIKE :query OR r.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function getDuplicateReclamationIdsToExclude(): array
    {
        $duplicatePairsQuery = $this->createQueryBuilder('r_dup')
            ->select('r_dup.titre, r_dup.description')
            ->groupBy('r_dup.titre, r_dup.description')
            ->having('COUNT(r_dup.id) > 1')
            ->getQuery();

        $duplicatePairs = $duplicatePairsQuery->getArrayResult();

        if (empty($duplicatePairs)) {
            return [];
        }

        $duplicateIds = [];

        foreach ($duplicatePairs as $pair) {
            $reclamationsInGroup = $this->createQueryBuilder('r_group')
                ->select('r_group.id')
                ->where('r_group.titre = :titre')
                ->andWhere('r_group.description = :description')
                ->setParameter('titre', $pair['titre'])
                ->setParameter('description', $pair['description'])
                ->orderBy('r_group.id', 'ASC')
                ->getQuery()
                ->getArrayResult();

            // Add all but the first reclamation's ID to the list of duplicates
            if (count($reclamationsInGroup) > 1) {
                foreach (array_slice($reclamationsInGroup, 1) as $reclamation) {
                    $duplicateIds[] = $reclamation['id'];
                }
            }
        }

        return $duplicateIds;
    }

    /**
     * Finds duplicate reclamations based on titre and description,
     * returning all but the first (lowest ID) instance of each duplicate group.
     *
     * @return Reclamation[]
     */
    public function findDuplicateReclamationsToDelete(): array
    {
        // Step 1: Find all (titre, description) pairs that have duplicates
        $duplicatePairsQuery = $this->createQueryBuilder('r_dup')
            ->select('r_dup.titre, r_dup.description')
            ->groupBy('r_dup.titre, r_dup.description')
            ->having('COUNT(r_dup.id) > 1')
            ->getQuery();

        $duplicatePairs = $duplicatePairsQuery->getArrayResult();

        if (empty($duplicatePairs)) {
            return []; // No duplicate pairs found
        }

        $reclamationsToDelete = [];

        // Step 2: For each duplicate pair, find all reclamations and select all but the first (lowest ID)
        foreach ($duplicatePairs as $pair) {
            $reclamationsInGroup = $this->createQueryBuilder('r_group')
                ->where('r_group.titre = :titre')
                ->andWhere('r_group.description = :description')
                ->setParameter('titre', $pair['titre'])
                ->setParameter('description', $pair['description'])
                ->orderBy('r_group.id', 'ASC') // Keep the one with the lowest ID
                ->getQuery()
                ->getResult();

            // Add all but the first reclamation in the group to the list of those to delete
            if (count($reclamationsInGroup) > 1) {
                $reclamationsToDelete = array_merge($reclamationsToDelete, array_slice($reclamationsInGroup, 1));
            }
        }

        return $reclamationsToDelete;
    }
}
