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
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve tous les utilisateurs actifs (non bannis)
     * 
     * @return User[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isBanned = :banned')
            ->setParameter('banned', false)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs bannis
     * 
     * @return User[]
     */
    public function findAllBanned(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isBanned = :banned')
            ->setParameter('banned', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total d'utilisateurs
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les utilisateurs actifs
     */
    public function countActive(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isBanned = :banned')
            ->setParameter('banned', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les derniers inscrits
     * 
     * @return User[]
     */
    public function findLatestRegistered(int $limit = 5): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par email ou username
     * 
     * @return User[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :query OR u.username LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques pour le dashboard admin
     * 
     * @return array{total: int, active: int, banned: int, admins: int, thisMonth: int}
     */
    public function getStatistics(): array
    {
        $total = $this->countAll();
        $active = $this->countActive();
        $banned = $total - $active;
        
        // Compte les admins
        $admins = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Inscrits ce mois
        $firstDayOfMonth = new \DateTimeImmutable('first day of this month midnight');
        $thisMonth = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.createdAt >= :firstDay')
            ->setParameter('firstDay', $firstDayOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
        
        return [
            'total' => $total,
            'active' => $active,
            'banned' => $banned,
            'admins' => $admins,
            'thisMonth' => $thisMonth,
        ];
    }
}
