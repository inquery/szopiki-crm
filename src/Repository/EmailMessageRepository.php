<?php

namespace App\Repository;

use App\Entity\EmailMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmailMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailMessage::class);
    }

    public function findFiltered(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.receivedAt', 'DESC');

        if (!empty($filters['account'])) {
            $qb->andWhere('e.emailAccount = :account')->setParameter('account', $filters['account']);
        }
        if (!empty($filters['folder'])) {
            $qb->andWhere('e.folder = :folder')->setParameter('folder', $filters['folder']);
        }
        if (!empty($filters['client'])) {
            $qb->andWhere('e.client = :client')->setParameter('client', $filters['client']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('e.subject LIKE :search OR e.fromAddress LIKE :search OR e.bodyText LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        $total = (clone $qb)->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();

        $results = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['data' => $results, 'total' => (int) $total, 'page' => $page, 'limit' => $limit];
    }

    public function findByMessageId(string $messageId): ?EmailMessage
    {
        return $this->findOneBy(['messageId' => $messageId]);
    }

    public function findThread(string $messageId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.messageId = :mid OR e.inReplyTo = :mid')
            ->setParameter('mid', $messageId)
            ->orderBy('e.receivedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
