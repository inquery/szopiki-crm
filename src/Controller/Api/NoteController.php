<?php

namespace App\Controller\Api;

use App\Entity\Note;
use App\Repository\ClientRepository;
use App\Repository\DealRepository;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes')]
#[OA\Tag(name: 'Notes')]
class NoteController extends AbstractController
{
    public function __construct(
        private NoteRepository $noteRepository,
        private ClientRepository $clientRepository,
        private DealRepository $dealRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(summary: 'List all notes')]
    #[OA\Response(response: 200, description: 'List of notes')]
    public function index(): JsonResponse
    {
        $notes = $this->noteRepository->findAll();

        $data = array_map(fn(Note $n) => [
            'id' => $n->getId(),
            'title' => $n->getTitle(),
            'content' => $n->getContent(),
            'type' => $n->getType(),
            'client' => $n->getClient() ? [
                'id' => $n->getClient()->getId(),
                'company_name' => $n->getClient()->getCompanyName(),
                'contact_person' => $n->getClient()->getContactPerson(),
            ] : null,
            'deal' => $n->getDeal() ? [
                'id' => $n->getDeal()->getId(),
                'title' => $n->getDeal()->getTitle(),
            ] : null,
            'createdAt' => $n->getCreatedAt()?->format('c'),
            'updatedAt' => $n->getUpdatedAt()?->format('c'),
        ], $notes);

        return new JsonResponse($data);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new note')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'client_id', type: 'integer'),
        new OA\Property(property: 'deal_id', type: 'integer'),
    ]))]
    #[OA\Response(response: 201, description: 'Note created')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $note = new Note();
        $note->setTitle($data['title'] ?? '');
        $note->setContent($data['content'] ?? '');
        $note->setType($data['type'] ?? 'general');

        if (isset($data['client_id'])) {
            $client = $this->clientRepository->find($data['client_id']);
            if (!$client) {
                return new JsonResponse(['error' => 'Client not found.'], 404);
            }
            $note->setClient($client);
        }

        if (isset($data['deal_id'])) {
            $deal = $this->dealRepository->find($data['deal_id']);
            if (!$deal) {
                return new JsonResponse(['error' => 'Deal not found.'], 404);
            }
            $note->setDeal($deal);
        }

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'type' => $note->getType(),
            'createdAt' => $note->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show a note')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Note details')]
    #[OA\Response(response: 404, description: 'Note not found')]
    public function show(int $id): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found.'], 404);
        }

        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'type' => $note->getType(),
            'client' => $note->getClient() ? [
                'id' => $note->getClient()->getId(),
                'company_name' => $note->getClient()->getCompanyName(),
                'contact_person' => $note->getClient()->getContactPerson(),
            ] : null,
            'deal' => $note->getDeal() ? [
                'id' => $note->getDeal()->getId(),
                'title' => $note->getDeal()->getTitle(),
            ] : null,
            'createdAt' => $note->getCreatedAt()?->format('c'),
            'updatedAt' => $note->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update a note')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Note updated')]
    #[OA\Response(response: 404, description: 'Note not found')]
    public function update(int $id, Request $request): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $note->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $note->setContent($data['content']);
        }
        if (isset($data['type'])) {
            $note->setType($data['type']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'type' => $note->getType(),
            'createdAt' => $note->getCreatedAt()?->format('c'),
            'updatedAt' => $note->getUpdatedAt()?->format('c'),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete a note')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Note deleted')]
    #[OA\Response(response: 404, description: 'Note not found')]
    public function delete(int $id): JsonResponse
    {
        $note = $this->noteRepository->find($id);

        if (!$note) {
            return new JsonResponse(['error' => 'Note not found.'], 404);
        }

        $this->entityManager->remove($note);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }
}
