<?php

namespace App\Controller\Api;

use App\Entity\Deal;
use App\Repository\ClientRepository;
use App\Repository\DealRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/deals')]
#[OA\Tag(name: 'Deals')]
class DealController extends AbstractController
{
    public function __construct(
        private DealRepository $dealRepository,
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(summary: 'List deals with optional filters')]
    #[OA\Parameter(name: 'stage', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'client_id', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of deals')]
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'stage' => $request->query->get('stage'),
            'status' => $request->query->get('status'),
            'client_id' => $request->query->get('client_id'),
            'search' => $request->query->get('search'),
        ];

        $deals = $this->dealRepository->findFiltered(array_filter($filters));

        $data = array_map(fn(Deal $d) => [
            'id' => $d->getId(),
            'title' => $d->getTitle(),
            'description' => $d->getDescription(),
            'value' => $d->getValue(),
            'stage' => $d->getStage(),
            'client_name' => $d->getClient()?->getCompanyName(),
            'client' => $d->getClient() ? [
                'id' => $d->getClient()->getId(),
                'contact_person' => $d->getClient()->getContactPerson(),
                'company_name' => $d->getClient()->getCompanyName(),
            ] : null,
            'assignedUser' => $d->getAssignedUser() ? [
                'id' => $d->getAssignedUser()->getId(),
                'firstName' => $d->getAssignedUser()->getFirstName(),
                'lastName' => $d->getAssignedUser()->getLastName(),
            ] : null,
            'expectedCloseDate' => $d->getExpectedCloseDate()?->format('Y-m-d'),
            'createdAt' => $d->getCreatedAt()?->format('c'),
            'updatedAt' => $d->getUpdatedAt()?->format('c'),
        ], $deals);

        return new JsonResponse($data);
    }

    #[Route('/pipeline', methods: ['GET'])]
    #[OA\Get(summary: 'Get deals grouped by stage (pipeline view)')]
    #[OA\Response(response: 200, description: 'Deals grouped by stage')]
    public function pipeline(): JsonResponse
    {
        $deals = $this->dealRepository->findAll();

        $pipeline = [];
        foreach ($deals as $deal) {
            $stage = $deal->getStage();
            if (!isset($pipeline[$stage])) {
                $pipeline[$stage] = [];
            }
            $pipeline[$stage][] = [
                'id' => $deal->getId(),
                'title' => $deal->getTitle(),
                'value' => $deal->getValue(),
                'client_name' => $deal->getClient()?->getCompanyName(),
                'client' => $deal->getClient() ? [
                    'id' => $deal->getClient()->getId(),
                    'contact_person' => $deal->getClient()->getContactPerson(),
                    'company_name' => $deal->getClient()->getCompanyName(),
                ] : null,
                'expectedCloseDate' => $deal->getExpectedCloseDate()?->format('Y-m-d'),
                'createdAt' => $deal->getCreatedAt()?->format('c'),
            ];
        }

        return new JsonResponse($pipeline);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new deal')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'value', type: 'number'),
        new OA\Property(property: 'stage', type: 'string'),
        new OA\Property(property: 'client_id', type: 'integer'),
        new OA\Property(property: 'expected_close_date', type: 'string', format: 'date'),
    ]))]
    #[OA\Response(response: 201, description: 'Deal created')]
    #[OA\Response(response: 400, description: 'Validation error')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['client_id'])) {
            return new JsonResponse(['error' => 'client_id is required.'], 400);
        }

        $client = $this->clientRepository->find($data['client_id']);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $deal = new Deal();
        $deal->setTitle($data['title'] ?? '');
        $deal->setDescription($data['description'] ?? null);
        $deal->setValue($data['value'] ?? 0);
        $deal->setStage($data['stage'] ?? 'lead');
        $deal->setStatus('open');
        $deal->setClient($client);

        if (isset($data['expected_close_date'])) {
            $deal->setExpectedCloseDate(new \DateTimeImmutable($data['expected_close_date']));
        }

        $this->entityManager->persist($deal);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $deal->getId(),
            'title' => $deal->getTitle(),
            'description' => $deal->getDescription(),
            'value' => $deal->getValue(),
            'stage' => $deal->getStage(),
            'client_name' => $deal->getClient()?->getCompanyName(),
            'client' => [
                'id' => $client->getId(),
                'company_name' => $client->getCompanyName(),
                'contact_person' => $client->getContactPerson(),
            ],
            'expectedCloseDate' => $deal->getExpectedCloseDate()?->format('Y-m-d'),
            'createdAt' => $deal->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show a deal')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Deal details')]
    #[OA\Response(response: 404, description: 'Deal not found')]
    public function show(int $id): JsonResponse
    {
        $deal = $this->dealRepository->find($id);

        if (!$deal) {
            return new JsonResponse(['error' => 'Deal not found.'], 404);
        }

        return new JsonResponse([
            'id' => $deal->getId(),
            'title' => $deal->getTitle(),
            'description' => $deal->getDescription(),
            'value' => $deal->getValue(),
            'stage' => $deal->getStage(),
            'client_name' => $deal->getClient()?->getCompanyName(),
            'client' => $deal->getClient() ? [
                'id' => $deal->getClient()->getId(),
                'contact_person' => $deal->getClient()->getContactPerson(),
                'company_name' => $deal->getClient()->getCompanyName(),
            ] : null,
            'assignedUser' => $deal->getAssignedUser() ? [
                'id' => $deal->getAssignedUser()->getId(),
                'firstName' => $deal->getAssignedUser()->getFirstName(),
                'lastName' => $deal->getAssignedUser()->getLastName(),
            ] : null,
            'expectedCloseDate' => $deal->getExpectedCloseDate()?->format('Y-m-d'),
            'createdAt' => $deal->getCreatedAt()?->format('c'),
            'updatedAt' => $deal->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a deal')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Deal updated')]
    #[OA\Response(response: 404, description: 'Deal not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $deal = $this->dealRepository->find($id);

        if (!$deal) {
            return new JsonResponse(['error' => 'Deal not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $deal->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $deal->setDescription($data['description']);
        }
        if (isset($data['value'])) {
            $deal->setValue($data['value']);
        }
        if (isset($data['stage'])) {
            $deal->setStage($data['stage']);
        }
        if (isset($data['status'])) {
            $deal->setStatus($data['status']);
        }
        if (isset($data['expected_close_date'])) {
            $deal->setExpectedCloseDate(new \DateTimeImmutable($data['expected_close_date']));
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $deal->getId(),
            'title' => $deal->getTitle(),
            'description' => $deal->getDescription(),
            'value' => $deal->getValue(),
            'stage' => $deal->getStage(),
            'client_name' => $deal->getClient()?->getCompanyName(),
            'expectedCloseDate' => $deal->getExpectedCloseDate()?->format('Y-m-d'),
            'createdAt' => $deal->getCreatedAt()?->format('c'),
            'updatedAt' => $deal->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete a deal')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Deal deleted')]
    #[OA\Response(response: 404, description: 'Deal not found')]
    public function delete(int $id): JsonResponse
    {
        $deal = $this->dealRepository->find($id);

        if (!$deal) {
            return new JsonResponse(['error' => 'Deal not found.'], 404);
        }

        $this->entityManager->remove($deal);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/stage', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Update deal stage only')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'stage', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Stage updated')]
    #[OA\Response(response: 404, description: 'Deal not found')]
    public function updateStage(int $id, Request $request): JsonResponse
    {
        $deal = $this->dealRepository->find($id);

        if (!$deal) {
            return new JsonResponse(['error' => 'Deal not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['stage'])) {
            return new JsonResponse(['error' => 'Stage is required.'], 400);
        }

        $deal->setStage($data['stage']);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $deal->getId(),
            'title' => $deal->getTitle(),
            'stage' => $deal->getStage(),
            'updatedAt' => $deal->getUpdatedAt()?->format('c'),
        ]);
    }
}
