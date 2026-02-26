<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $hashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($hashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findActiveUsers(): array
    {
        return $this->findBy(['isActive' => true], ['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    /**
     * Search users by criteria (firstName, lastName, email) and filter by role/status.
     *
     * @param array $criteria ['q' => string, 'role' => string|null, 'status' => string|null]
     * @param array $orderBy ['field' => 'ASC|DESC']
     */
    public function searchUsers(array $criteria = [], array $orderBy = []): array
    {
        $qb = $this->createQueryBuilder('u');

        if (!empty($criteria['q'])) {
            $qb->andWhere('u.firstName LIKE :q OR u.lastName LIKE :q OR u.email LIKE :q')
               ->setParameter('q', '%'.$criteria['q'].'%');
        }

        if (!empty($criteria['role'])) {
            // Roles are stored as JSON array in the DB. Doctrine DQL doesn't support
            // the native JSON_CONTAINS() function, so match the JSON text using LIKE
            // to find the role name (including quotes) inside the JSON array.
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%"' . $criteria['role'] . '"%');
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('u.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        // Apply ordering
        foreach ($orderBy as $field => $dir) {
            $allowed = ['firstName','lastName','createdAt','updatedAt','email'];
            if (in_array($field, $allowed, true)) {
                $qb->addOrderBy('u.'.$field, $dir);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findAll(): array
    {
        return parent::findBy([], ['firstName' => 'ASC', 'lastName' => 'ASC']);
    }
}
