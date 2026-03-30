<?php

namespace App\Controller\Api;

use App\Entity\PanelConfig;
use App\Repository\PanelConfigRepository;
use App\Service\EncryptionService;
use App\Service\PanelInstallationService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/panel')]
#[OA\Tag(name: 'Panel')]
class PanelController extends AbstractController
{
    public function __construct(
        private PanelConfigRepository $panelConfigRepository,
        private EncryptionService $encryptionService,
        private PanelInstallationService $panelInstallationService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/config', methods: ['GET'])]
    #[OA\Get(summary: 'List panel configurations')]
    #[OA\Response(response: 200, description: 'List of panel configs')]
    public function listConfigs(): JsonResponse
    {
        $configs = $this->panelConfigRepository->findAll();

        $data = array_map(fn(PanelConfig $c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
            'type' => $c->getType(),
            'hostname' => $c->getHostname(),
            'port' => $c->getPort(),
            'isActive' => $c->isActive(),
            'createdAt' => $c->getCreatedAt()?->format('c'),
            'updatedAt' => $c->getUpdatedAt()?->format('c'),
        ], $configs);

        return new JsonResponse($data);
    }

    #[Route('/config', methods: ['POST'])]
    #[OA\Post(summary: 'Create a panel configuration')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'hostname', type: 'string'),
        new OA\Property(property: 'port', type: 'integer'),
        new OA\Property(property: 'username', type: 'string'),
        new OA\Property(property: 'password', type: 'string'),
        new OA\Property(property: 'api_key', type: 'string'),
        new OA\Property(property: 'api_secret', type: 'string'),
    ]))]
    #[OA\Response(response: 201, description: 'Panel config created')]
    public function createConfig(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $config = new PanelConfig();
        $config->setName($data['name'] ?? '');
        $config->setType($data['type'] ?? '');
        $config->setHostname($data['hostname'] ?? '');
        $config->setPort($data['port'] ?? null);
        $config->setIsActive(true);

        // Encrypt all credential fields
        if (isset($data['username'])) {
            $config->setUsername($this->encryptionService->encrypt($data['username']));
        }
        if (isset($data['password'])) {
            $config->setPassword($this->encryptionService->encrypt($data['password']));
        }
        if (isset($data['api_key'])) {
            $config->setApiKey($this->encryptionService->encrypt($data['api_key']));
        }
        if (isset($data['api_secret'])) {
            $config->setApiSecret($this->encryptionService->encrypt($data['api_secret']));
        }

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $config->getId(),
            'name' => $config->getName(),
            'type' => $config->getType(),
            'hostname' => $config->getHostname(),
            'port' => $config->getPort(),
            'isActive' => $config->isActive(),
            'createdAt' => $config->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/config/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a panel configuration')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Panel config updated')]
    #[OA\Response(response: 404, description: 'Config not found')]
    public function updateConfig(int $id, Request $request): JsonResponse
    {
        $config = $this->panelConfigRepository->find($id);

        if (!$config) {
            return new JsonResponse(['error' => 'Panel config not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $config->setName($data['name']);
        }
        if (isset($data['type'])) {
            $config->setType($data['type']);
        }
        if (isset($data['hostname'])) {
            $config->setHostname($data['hostname']);
        }
        if (isset($data['port'])) {
            $config->setPort($data['port']);
        }
        if (isset($data['username'])) {
            $config->setUsername($this->encryptionService->encrypt($data['username']));
        }
        if (isset($data['password'])) {
            $config->setPassword($this->encryptionService->encrypt($data['password']));
        }
        if (isset($data['api_key'])) {
            $config->setApiKey($this->encryptionService->encrypt($data['api_key']));
        }
        if (isset($data['api_secret'])) {
            $config->setApiSecret($this->encryptionService->encrypt($data['api_secret']));
        }
        if (isset($data['is_active'])) {
            $config->setIsActive($data['is_active']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $config->getId(),
            'name' => $config->getName(),
            'type' => $config->getType(),
            'hostname' => $config->getHostname(),
            'port' => $config->getPort(),
            'isActive' => $config->isActive(),
            'updatedAt' => $config->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/install/{id}', methods: ['POST'])]
    #[OA\Post(summary: 'Install a panel')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Installation triggered')]
    #[OA\Response(response: 404, description: 'Config not found')]
    #[OA\Response(response: 501, description: 'Not implemented')]
    public function install(int $id): JsonResponse
    {
        $config = $this->panelConfigRepository->find($id);

        if (!$config) {
            return new JsonResponse(['error' => 'Panel config not found.'], 404);
        }

        try {
            $result = $this->panelInstallationService->install($config);

            return new JsonResponse([
                'message' => 'Installation completed.',
                'result' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 501);
        }
    }

    #[Route('/navigate/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Get navigation URL for a panel')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'target', in: 'query', schema: new OA\Schema(type: 'string', enum: ['panel', 'database']))]
    #[OA\Response(response: 200, description: 'Navigation URL')]
    #[OA\Response(response: 404, description: 'Config not found')]
    public function navigate(int $id, Request $request): JsonResponse
    {
        $config = $this->panelConfigRepository->find($id);

        if (!$config) {
            return new JsonResponse(['error' => 'Panel config not found.'], 404);
        }

        $target = $request->query->get('target', 'panel');
        $hostname = $config->getHostname();
        $port = $config->getPort();

        $url = match ($target) {
            'database' => sprintf('https://%s:%d/phpmyadmin', $hostname, $port),
            default => sprintf('https://%s:%d', $hostname, $port),
        };

        return new JsonResponse([
            'url' => $url,
            'target' => $target,
            'config_id' => $config->getId(),
        ]);
    }
}
