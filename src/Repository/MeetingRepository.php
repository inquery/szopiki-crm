<?php

namespace App\Repository;

use App\Entity\Meeting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    public function findFiltered(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.startAt', 'DESC');

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('m.startAt >= :dateFrom')->setParameter('dateFrom', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $qb->andWhere('m.startAt <= :dateTo')->setParameter('dateTo', $filters['dateTo']);
        }
        if (!empty($filters['client'])) {
            $qb->andWhere('m.client = :client')->setParameter('client', $filters['client']);
        }
        if (!empty($filters['status'])) {
            $qb->andWhere('m.status = :status')->setParameter('status', $filters['status']);
        }

        $total = (clone $qb)->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

        $results = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['data' => $results, 'total' => (int) $total, 'page' => $page, 'limit' => $limit];
    }

    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.startAt >= :start AND m.startAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('m.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.startAt >= :now')
            ->andWhere('m.status = :status')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('status', Meeting::STATUS_SCHEDULED)
            ->orderBy('m.startAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
