<?php

namespace App\Repository;

use App\Entity\EmailAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmailAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailAccount::class);
    }

    public function findActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
