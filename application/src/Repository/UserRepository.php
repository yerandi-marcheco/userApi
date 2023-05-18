<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $cacheKey = 'user_list';
    public const ONE_HOUR_CACHE = 3600;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return User[]
     * @throws \Exception
     */
    public function findByFilters(array $filters, int $page = 1, int $perPage = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $this->setFilters($queryBuilder, $filters);

        $queryBuilder->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countByFilters(array $filters): int
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $this->setFilters($queryBuilder, $filters);

        return (int) $queryBuilder->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCacheKey(array $filters, int $page, int $pagination): string
    {
        $filtersKeys = implode('_', $filters);

        return $this->cacheKey .= "-$filtersKeys-$page-$pagination";
    }

    private function setFilters(&$queryBuilder, array $filters): void
    {
        if (isset($filters['isActive'])) {
            $queryBuilder->andWhere('u.is_active = :isActive')
                ->setParameter('isActive', (bool)$filters['isActive']);
        }

        if (isset($filters['isMember'])) {
            $queryBuilder->andWhere('u.is_member = :isMember')
                ->setParameter('isMember', (bool)$filters['isMember']);
        }

        if (isset($filters['lastLoginAt'])) {
            $lastLoginAt = explode('to', $filters['lastLoginAt']);
            if (isset($lastLoginAt[0])) {
                $queryBuilder->andWhere('u.last_login_at >= :fromLastLoginAt')
                    ->setParameter('fromLastLoginAt', $lastLoginAt[0]);
            }
            if (isset($lastLoginAt[1])) {
                $queryBuilder->andWhere('u.last_login_at <= :fromLastLoginTo')
                    ->setParameter('fromLastLoginTo', $lastLoginAt[1]);
            }
        }

        if (isset($filters['userType'])) {
            $userTypes = $filters['userType'];
            $queryBuilder->andWhere('u.user_type IN (:userTypes)')
                ->setParameter('userTypes', $userTypes);
        }
    }
}
