<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/clients')]
#[OA\Tag(name: 'Clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(summary: 'List clients with optional filters')]
    #[OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of clients')]
    public function index(Request $request): JsonResponse
    {
        $rawStatus = $request->query->get('status');
        if (is_array($rawStatus)) {
            $status = $rawStatus;
        } elseif ($rawStatus === 'clients') {
            $status = ['active', 'implementing'];
        } elseif ($rawStatus) {
            $status = $rawStatus;
        } else {
            $status = null;
        }
        $filters = [
            'status' => $status,
            'search' => $request->query->get('search'),
        ];

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 25)));

        $result = $this->clientRepository->findFiltered(array_filter($filters), $page, $limit);

        $data = array_map(fn(Client $c) => [
            'id' => $c->getId(),
            'company_name' => $c->getCompanyName(),
            'tax_id' => $c->getTaxId(),
            'contact_person' => $c->getContactPerson(),
            'email' => $c->getEmail(),
            'phone' => $c->getPhone(),
            'address' => $c->getAddress(),
            'city' => $c->getCity(),
            'postal_code' => $c->getPostalCode(),
            'country' => $c->getCountry(),
            'status' => $c->getStatus(),
            'source' => $c->getSource(),
            'notes' => $c->getNotes(),
            'assigned_user_id' => $c->getAssignedUser()?->getId(),
            'assigned_user_name' => $c->getAssignedUser() ? $c->getAssignedUser()->getFirstName() . ' ' . $c->getAssignedUser()->getLastName() : null,
            'resigned_at' => $c->getResignedAt()?->format('c'),
            'deletion_date' => $c->getDeletionDate()?->format('Y-m-d'),
            'created_at' => $c->getCreatedAt()?->format('c'),
            'updated_at' => $c->getUpdatedAt()?->format('c'),
        ], $result['data']);

        return new JsonResponse([
            'data' => $data,
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
                'pages' => (int) ceil($result['total'] / $result['limit']),
            ],
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new client')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'company_name', type: 'string'),
        new OA\Property(property: 'tax_id', type: 'string'),
        new OA\Property(property: 'contact_person', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'address', type: 'string'),
        new OA\Property(property: 'city', type: 'string'),
        new OA\Property(property: 'postal_code', type: 'string'),
        new OA\Property(property: 'country', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'source', type: 'string'),
        new OA\Property(property: 'notes', type: 'string'),
    ]))]
    #[OA\Response(response: 201, description: 'Client created')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setCompanyName($data['company_name'] ?? '');
        $client->setTaxId($data['tax_id'] ?? null);
        $client->setContactPerson($data['contact_person'] ?? null);
        $client->setEmail($data['email'] ?? null);
        $client->setPhone($data['phone'] ?? null);
        $client->setAddress($data['address'] ?? null);
        $client->setCity($data['city'] ?? null);
        $client->setPostalCode($data['postal_code'] ?? null);
        $client->setCountry($data['country'] ?? 'Polska');
        $client->setStatus($data['status'] ?? 'prospect');
        $client->setSource($data['source'] ?? null);
        $client->setNotes($data['notes'] ?? null);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $client->getId(),
            'company_name' => $client->getCompanyName(),
            'tax_id' => $client->getTaxId(),
            'contact_person' => $client->getContactPerson(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'address' => $client->getAddress(),
            'city' => $client->getCity(),
            'postal_code' => $client->getPostalCode(),
            'country' => $client->getCountry(),
            'status' => $client->getStatus(),
            'source' => $client->getSource(),
            'notes' => $client->getNotes(),
            'createdAt' => $client->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show a client with related counts')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Client details')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        return new JsonResponse([
            'id' => $client->getId(),
            'company_name' => $client->getCompanyName(),
            'tax_id' => $client->getTaxId(),
            'contact_person' => $client->getContactPerson(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'address' => $client->getAddress(),
            'city' => $client->getCity(),
            'postal_code' => $client->getPostalCode(),
            'country' => $client->getCountry(),
            'status' => $client->getStatus(),
            'source' => $client->getSource(),
            'notes' => $client->getNotes(),
            'assigned_user_id' => $client->getAssignedUser()?->getId(),
            'assigned_user_name' => $client->getAssignedUser() ? $client->getAssignedUser()->getFirstName() . ' ' . $client->getAssignedUser()->getLastName() : null,
            'resigned_at' => $client->getResignedAt()?->format('c'),
            'deletion_date' => $client->getDeletionDate()?->format('Y-m-d'),
            'created_at' => $client->getCreatedAt()?->format('c'),
            'updated_at' => $client->getUpdatedAt()?->format('c'),
            'counts' => [
                'deals' => $client->getDeals()->count(),
                'notes' => $client->getNoteEntries()->count(),
                'meetings' => $client->getMeetings()->count(),
            ],
        ]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a client')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Client updated')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['company_name'])) {
            $client->setCompanyName($data['company_name']);
        }
        if (isset($data['tax_id'])) {
            $client->setTaxId($data['tax_id']);
        }
        if (isset($data['contact_person'])) {
            $client->setContactPerson($data['contact_person']);
        }
        if (isset($data['email'])) {
            $client->setEmail($data['email']);
        }
        if (isset($data['phone'])) {
            $client->setPhone($data['phone']);
        }
        if (isset($data['address'])) {
            $client->setAddress($data['address']);
        }
        if (isset($data['city'])) {
            $client->setCity($data['city']);
        }
        if (isset($data['postal_code'])) {
            $client->setPostalCode($data['postal_code']);
        }
        if (isset($data['country'])) {
            $client->setCountry($data['country']);
        }
        if (isset($data['status'])) {
            $client->setStatus($data['status']);
        }
        if (isset($data['source'])) {
            $client->setSource($data['source']);
        }
        if (array_key_exists('notes', $data)) {
            $client->setNotes($data['notes']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $client->getId(),
            'company_name' => $client->getCompanyName(),
            'tax_id' => $client->getTaxId(),
            'contact_person' => $client->getContactPerson(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'address' => $client->getAddress(),
            'city' => $client->getCity(),
            'postal_code' => $client->getPostalCode(),
            'country' => $client->getCountry(),
            'status' => $client->getStatus(),
            'source' => $client->getSource(),
            'notes' => $client->getNotes(),
            'assigned_user_id' => $client->getAssignedUser()?->getId(),
            'assigned_user_name' => $client->getAssignedUser() ? $client->getAssignedUser()->getFirstName() . ' ' . $client->getAssignedUser()->getLastName() : null,
            'createdAt' => $client->getCreatedAt()?->format('c'),
            'updatedAt' => $client->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Soft delete a client (set inactive)')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Client deactivated')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $client->setStatus('deleted');
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Change client status')]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (!$newStatus || !in_array($newStatus, Client::STATUSES)) {
            return new JsonResponse(['error' => 'Invalid status.'], 400);
        }

        if (!$client->canTransitionTo($newStatus)) {
            $allowed = Client::TRANSITIONS[$client->getStatus()] ?? [];
            return new JsonResponse([
                'error' => 'Niedozwolone przejscie statusu.',
                'current' => $client->getStatus(),
                'allowed' => $allowed,
            ], 422);
        }

        $client->setStatus($newStatus);

        if ($newStatus === Client::STATUS_RESIGNED) {
            $client->setResignedAt(new \DateTimeImmutable());
        } else {
            $client->setResignedAt(null);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'status' => $client->getStatus(),
            'resigned_at' => $client->getResignedAt()?->format('c'),
            'deletion_date' => $client->getDeletionDate()?->format('Y-m-d'),
        ]);
    }

    #[Route('/{id}/deals', methods: ['GET'])]
    #[OA\Get(summary: 'Get deals for a client')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'List of deals')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function deals(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $data = [];
        foreach ($client->getDeals() as $deal) {
            $data[] = [
                'id' => $deal->getId(),
                'title' => $deal->getTitle(),
                'value' => $deal->getValue(),
                'stage' => $deal->getStage(),
                'status' => $deal->getStatus(),
                'createdAt' => $deal->getCreatedAt()?->format('c'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}/notes', methods: ['GET'])]
    #[OA\Get(summary: 'Get notes for a client')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'List of notes')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function notes(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $data = [];
        foreach ($client->getNoteEntries() as $note) {
            $data[] = [
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'content' => $note->getContent(),
                'type' => $note->getType(),
                'createdAt' => $note->getCreatedAt()?->format('c'),
                'updatedAt' => $note->getUpdatedAt()?->format('c'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}/meetings', methods: ['GET'])]
    #[OA\Get(summary: 'Get meetings for a client')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'List of meetings')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function meetings(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client not found.'], 404);
        }

        $data = [];
        foreach ($client->getMeetings() as $meeting) {
            $data[] = [
                'id' => $meeting->getId(),
                'title' => $meeting->getTitle(),
                'description' => $meeting->getDescription(),
                'startAt' => $meeting->getStartAt()?->format('c'),
                'endAt' => $meeting->getEndAt()?->format('c'),
                'status' => $meeting->getStatus(),
                'createdAt' => $meeting->getCreatedAt()?->format('c'),
            ];
        }

        return new JsonResponse($data);
    }
}
