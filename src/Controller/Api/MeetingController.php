<?php

namespace App\Controller\Api;

use App\Entity\Meeting;
use App\Entity\MeetingParticipant;
use App\Repository\ClientRepository;
use App\Repository\MeetingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/meetings')]
#[OA\Tag(name: 'Meetings')]
class MeetingController extends AbstractController
{
    public function __construct(
        private MeetingRepository $meetingRepository,
        private UserRepository $userRepository,
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(summary: 'List all meetings')]
    #[OA\Response(response: 200, description: 'List of meetings')]
    public function index(): JsonResponse
    {
        $meetings = $this->meetingRepository->findAll();

        $data = array_map(fn(Meeting $m) => [
            'id' => $m->getId(),
            'title' => $m->getTitle(),
            'description' => $m->getDescription(),
            'startAt' => $m->getStartAt()?->format('c'),
            'endAt' => $m->getEndAt()?->format('c'),
            'status' => $m->getStatus(),
            'location' => $m->getLocation(),
            'client' => $m->getClient() ? [
                'id' => $m->getClient()->getId(),
                'company_name' => $m->getClient()->getCompanyName(),
                'contact_person' => $m->getClient()->getContactPerson(),
            ] : null,
            'createdAt' => $m->getCreatedAt()?->format('c'),
        ], $meetings);

        return new JsonResponse($data);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new meeting')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'start_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'end_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'location', type: 'string'),
        new OA\Property(property: 'client_id', type: 'integer'),
        new OA\Property(property: 'participants', type: 'array', items: new OA\Items(properties: [
            new OA\Property(property: 'user_id', type: 'integer'),
            new OA\Property(property: 'external_name', type: 'string'),
            new OA\Property(property: 'external_email', type: 'string'),
        ])),
    ]))]
    #[OA\Response(response: 201, description: 'Meeting created')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $meeting = new Meeting();
        $meeting->setTitle($data['title'] ?? '');
        $meeting->setDescription($data['description'] ?? null);
        $meeting->setStartAt(new \DateTimeImmutable($data['start_at'] ?? 'now'));
        $meeting->setEndAt(new \DateTimeImmutable($data['end_at'] ?? 'now +1 hour'));
        $meeting->setLocation($data['location'] ?? null);
        $meeting->setStatus('scheduled');

        if (isset($data['client_id'])) {
            $client = $this->clientRepository->find($data['client_id']);
            if (!$client) {
                return new JsonResponse(['error' => 'Client not found.'], 404);
            }
            $meeting->setClient($client);
        }

        $this->entityManager->persist($meeting);

        if (isset($data['participants']) && is_array($data['participants'])) {
            foreach ($data['participants'] as $participantData) {
                $participant = new MeetingParticipant();
                $participant->setMeeting($meeting);

                if (isset($participantData['user_id'])) {
                    $user = $this->userRepository->find($participantData['user_id']);
                    if ($user) {
                        $participant->setUser($user);
                    }
                }

                if (isset($participantData['external_name'])) {
                    $participant->setExternalName($participantData['external_name']);
                }
                if (isset($participantData['external_email'])) {
                    $participant->setExternalEmail($participantData['external_email']);
                }

                $this->entityManager->persist($participant);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $meeting->getId(),
            'title' => $meeting->getTitle(),
            'description' => $meeting->getDescription(),
            'startAt' => $meeting->getStartAt()?->format('c'),
            'endAt' => $meeting->getEndAt()?->format('c'),
            'status' => $meeting->getStatus(),
            'location' => $meeting->getLocation(),
            'createdAt' => $meeting->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show a meeting')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Meeting details')]
    #[OA\Response(response: 404, description: 'Meeting not found')]
    public function show(int $id): JsonResponse
    {
        $meeting = $this->meetingRepository->find($id);

        if (!$meeting) {
            return new JsonResponse(['error' => 'Meeting not found.'], 404);
        }

        $participants = [];
        foreach ($meeting->getParticipants() as $p) {
            $participants[] = [
                'id' => $p->getId(),
                'user' => $p->getUser() ? [
                    'id' => $p->getUser()->getId(),
                    'firstName' => $p->getUser()->getFirstName(),
                    'lastName' => $p->getUser()->getLastName(),
                ] : null,
                'externalName' => $p->getExternalName(),
                'externalEmail' => $p->getExternalEmail(),
            ];
        }

        return new JsonResponse([
            'id' => $meeting->getId(),
            'title' => $meeting->getTitle(),
            'description' => $meeting->getDescription(),
            'startAt' => $meeting->getStartAt()?->format('c'),
            'endAt' => $meeting->getEndAt()?->format('c'),
            'status' => $meeting->getStatus(),
            'location' => $meeting->getLocation(),
            'client' => $meeting->getClient() ? [
                'id' => $meeting->getClient()->getId(),
                'company_name' => $meeting->getClient()->getCompanyName(),
                'contact_person' => $meeting->getClient()->getContactPerson(),
            ] : null,
            'participants' => $participants,
            'createdAt' => $meeting->getCreatedAt()?->format('c'),
            'updatedAt' => $meeting->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a meeting')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Meeting updated')]
    #[OA\Response(response: 404, description: 'Meeting not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $meeting = $this->meetingRepository->find($id);

        if (!$meeting) {
            return new JsonResponse(['error' => 'Meeting not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $meeting->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $meeting->setDescription($data['description']);
        }
        if (isset($data['start_at'])) {
            $meeting->setStartAt(new \DateTimeImmutable($data['start_at']));
        }
        if (isset($data['end_at'])) {
            $meeting->setEndAt(new \DateTimeImmutable($data['end_at']));
        }
        if (isset($data['location'])) {
            $meeting->setLocation($data['location']);
        }
        if (isset($data['status'])) {
            $meeting->setStatus($data['status']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $meeting->getId(),
            'title' => $meeting->getTitle(),
            'description' => $meeting->getDescription(),
            'startAt' => $meeting->getStartAt()?->format('c'),
            'endAt' => $meeting->getEndAt()?->format('c'),
            'status' => $meeting->getStatus(),
            'location' => $meeting->getLocation(),
            'createdAt' => $meeting->getCreatedAt()?->format('c'),
            'updatedAt' => $meeting->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete a meeting')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Meeting deleted')]
    #[OA\Response(response: 404, description: 'Meeting not found')]
    public function delete(int $id): JsonResponse
    {
        $meeting = $this->meetingRepository->find($id);

        if (!$meeting) {
            return new JsonResponse(['error' => 'Meeting not found.'], 404);
        }

        $this->entityManager->remove($meeting);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Update meeting status')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Status updated')]
    #[OA\Response(response: 404, description: 'Meeting not found')]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $meeting = $this->meetingRepository->find($id);

        if (!$meeting) {
            return new JsonResponse(['error' => 'Meeting not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required.'], 400);
        }

        $meeting->setStatus($data['status']);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $meeting->getId(),
            'title' => $meeting->getTitle(),
            'status' => $meeting->getStatus(),
            'updatedAt' => $meeting->getUpdatedAt()?->format('c'),
        ]);
    }
}
