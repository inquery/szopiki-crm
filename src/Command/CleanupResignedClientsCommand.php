<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:cleanup-resigned', description: 'Delete clients that resigned more than 30 days ago')]
class CleanupResignedClientsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cutoff = new \DateTimeImmutable('-30 days');

        $clients = $this->entityManager->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->andWhere('c.resignedAt IS NOT NULL')
            ->andWhere('c.resignedAt <= :cutoff')
            ->setParameter('status', Client::STATUS_RESIGNED)
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($clients as $client) {
            $client->setStatus(Client::STATUS_DELETED);
            $count++;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('Marked %d resigned client(s) as deleted.', $count));

        return Command::SUCCESS;
    }
}
