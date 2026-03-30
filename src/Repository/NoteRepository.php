<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function findFiltered(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC');

        if (!empty($filters['client'])) {
            $qb->andWhere('n.client = :client')->setParameter('client', $filters['client']);
        }
        if (!empty($filters['deal'])) {
            $qb->andWhere('n.deal = :deal')->setParameter('deal', $filters['deal']);
        }
        if (!empty($filters['type'])) {
            $qb->andWhere('n.type = :type')->setParameter('type', $filters['type']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('n.title LIKE :search OR n.content LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        $total = (clone $qb)->select('COUNT(n.id)')->getQuery()->getSingleScalarResult();

        $results = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['data' => $results, 'total' => (int) $total, 'page' => $page, 'limit' => $limit];
    }
}
