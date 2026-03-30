<?php

namespace App\Service;

use App\Entity\PanelConfig;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PanelInstallationService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     * @return array{success: bool, message: string, urls: array<string, string>}
     *
     * @throws \RuntimeException
     */
    public function install(PanelConfig $config, User $executor, array $options = []): array
    {
        throw new \RuntimeException('Panel installation not yet implemented.');
    }

    /**
     * @throws \RuntimeException
     */
    public function getNavigationUrl(PanelConfig $config, string $target = 'panel'): string
    {
        throw new \RuntimeException('Navigation not yet implemented.');
    }
}
