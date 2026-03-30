<?php

namespace App\Repository;

use App\Entity\Deal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deal::class);
    }

    public function findFiltered(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC');

        if (!empty($filters['stage'])) {
            $qb->andWhere('d.stage = :stage')->setParameter('stage', $filters['stage']);
        }
        if (!empty($filters['client'])) {
            $qb->andWhere('d.client = :client')->setParameter('client', $filters['client']);
        }
        if (!empty($filters['assignedUser'])) {
            $qb->andWhere('d.assignedUser = :user')->setParameter('user', $filters['assignedUser']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('d.title LIKE :search')->setParameter('search', '%' . $filters['search'] . '%');
        }

        $total = (clone $qb)->select('COUNT(d.id)')->getQuery()->getSingleScalarResult();

        $results = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['data' => $results, 'total' => (int) $total, 'page' => $page, 'limit' => $limit];
    }

    public function findPipeline(): array
    {
        $stages = [Deal::STAGE_LEAD, Deal::STAGE_PROPOSAL, Deal::STAGE_NEGOTIATION, Deal::STAGE_WON, Deal::STAGE_LOST];
        $pipeline = [];

        foreach ($stages as $stage) {
            $pipeline[$stage] = $this->createQueryBuilder('d')
                ->where('d.stage = :stage')
                ->setParameter('stage', $stage)
                ->orderBy('d.expectedCloseDate', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $pipeline;
    }
}
