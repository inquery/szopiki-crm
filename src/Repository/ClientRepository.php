<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findFiltered(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.status != :deleted')
            ->setParameter('deleted', Client::STATUS_DELETED)
            ->orderBy('c.createdAt', 'DESC');

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $qb->andWhere('c.status IN (:statuses)')->setParameter('statuses', $filters['status']);
            } else {
                $qb->andWhere('c.status = :status')->setParameter('status', $filters['status']);
            }
        }
        if (!empty($filters['assignedUser'])) {
            $qb->andWhere('c.assignedUser = :user')->setParameter('user', $filters['assignedUser']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('c.companyName LIKE :search OR c.contactPerson LIKE :search OR c.email LIKE :search OR c.taxId LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        $total = (clone $qb)->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $results = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['data' => $results, 'total' => (int) $total, 'page' => $page, 'limit' => $limit];
    }

    public function findByEmail(string $email): ?Client
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function countByStatus(string $status): int
    {
        return $this->count(['status' => $status]);
    }

    public function countByStatuses(array $statuses): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNonDeleted(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status != :deleted')
            ->setParameter('deleted', Client::STATUS_DELETED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
