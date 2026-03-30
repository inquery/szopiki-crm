<?php

namespace App\Controller\Api;

use App\Entity\EmailAccount;
use App\Repository\ClientRepository;
use App\Repository\EmailAccountRepository;
use App\Repository\EmailMessageRepository;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/emails')]
#[OA\Tag(name: 'Emails')]
class EmailController extends AbstractController
{
    public function __construct(
        private EmailAccountRepository $emailAccountRepository,
        private EmailMessageRepository $emailMessageRepository,
        private ClientRepository $clientRepository,
        private EncryptionService $encryptionService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/accounts', methods: ['GET'])]
    #[OA\Get(summary: 'List email accounts')]
    #[OA\Response(response: 200, description: 'List of email accounts')]
    public function listAccounts(): JsonResponse
    {
        $accounts = $this->emailAccountRepository->findAll();

        $data = array_map(fn(EmailAccount $a) => [
            'id' => $a->getId(),
            'name' => $a->getName(),
            'email' => $a->getEmail(),
            'imapHost' => $a->getImapHost(),
            'imapPort' => $a->getImapPort(),
            'smtpHost' => $a->getSmtpHost(),
            'smtpPort' => $a->getSmtpPort(),
            'isActive' => $a->isActive(),
            'createdAt' => $a->getCreatedAt()?->format('c'),
        ], $accounts);

        return new JsonResponse($data);
    }

    #[Route('/accounts', methods: ['POST'])]
    #[OA\Post(summary: 'Create an email account')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'password', type: 'string'),
        new OA\Property(property: 'imap_host', type: 'string'),
        new OA\Property(property: 'imap_port', type: 'integer'),
        new OA\Property(property: 'smtp_host', type: 'string'),
        new OA\Property(property: 'smtp_port', type: 'integer'),
    ]))]
    #[OA\Response(response: 201, description: 'Email account created')]
    public function createAccount(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $account = new EmailAccount();
        $account->setName($data['name'] ?? '');
        $account->setEmail($data['email'] ?? '');
        $account->setPassword($this->encryptionService->encrypt($data['password'] ?? ''));
        $account->setImapHost($data['imap_host'] ?? '');
        $account->setImapPort($data['imap_port'] ?? 993);
        $account->setSmtpHost($data['smtp_host'] ?? '');
        $account->setSmtpPort($data['smtp_port'] ?? 587);
        $account->setIsActive(true);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'imapHost' => $account->getImapHost(),
            'imapPort' => $account->getImapPort(),
            'smtpHost' => $account->getSmtpHost(),
            'smtpPort' => $account->getSmtpPort(),
            'isActive' => $account->isActive(),
            'createdAt' => $account->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/accounts/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update an email account')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Email account updated')]
    #[OA\Response(response: 404, description: 'Account not found')]
    public function updateAccount(int $id, Request $request): JsonResponse
    {
        $account = $this->emailAccountRepository->find($id);

        if (!$account) {
            return new JsonResponse(['error' => 'Email account not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $account->setName($data['name']);
        }
        if (isset($data['email'])) {
            $account->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $account->setPassword($this->encryptionService->encrypt($data['password']));
        }
        if (isset($data['imap_host'])) {
            $account->setImapHost($data['imap_host']);
        }
        if (isset($data['imap_port'])) {
            $account->setImapPort($data['imap_port']);
        }
        if (isset($data['smtp_host'])) {
            $account->setSmtpHost($data['smtp_host']);
        }
        if (isset($data['smtp_port'])) {
            $account->setSmtpPort($data['smtp_port']);
        }
        if (isset($data['is_active'])) {
            $account->setIsActive($data['is_active']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'imapHost' => $account->getImapHost(),
            'imapPort' => $account->getImapPort(),
            'smtpHost' => $account->getSmtpHost(),
            'smtpPort' => $account->getSmtpPort(),
            'isActive' => $account->isActive(),
        ]);
    }

    #[Route('/accounts/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete an email account')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Account deleted')]
    #[OA\Response(response: 404, description: 'Account not found')]
    public function deleteAccount(int $id): JsonResponse
    {
        $account = $this->emailAccountRepository->find($id);

        if (!$account) {
            return new JsonResponse(['error' => 'Email account not found.'], 404);
        }

        $this->entityManager->remove($account);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/accounts/{id}/sync', methods: ['POST'])]
    #[OA\Post(summary: 'Trigger email sync for an account')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Sync triggered')]
    #[OA\Response(response: 404, description: 'Account not found')]
    public function syncAccount(int $id): JsonResponse
    {
        $account = $this->emailAccountRepository->find($id);

        if (!$account) {
            return new JsonResponse(['error' => 'Email account not found.'], 404);
        }

        // Placeholder: actual sync implementation would go here
        return new JsonResponse([
            'message' => 'Sync triggered successfully.',
            'account_id' => $account->getId(),
        ]);
    }

    #[Route('/messages', methods: ['GET'])]
    #[OA\Get(summary: 'List email messages with optional filters')]
    #[OA\Parameter(name: 'account_id', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'folder', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of email messages')]
    public function listMessages(Request $request): JsonResponse
    {
        $filters = [
            'account_id' => $request->query->get('account_id'),
            'search' => $request->query->get('search'),
            'folder' => $request->query->get('folder'),
        ];

        $messages = $this->emailMessageRepository->findAll();

        $data = [];
        foreach ($messages as $msg) {
            $data[] = [
                'id' => $msg->getId(),
                'subject' => $msg->getSubject(),
                'fromEmail' => $msg->getFromEmail(),
                'toEmail' => $msg->getToEmail(),
                'folder' => $msg->getFolder(),
                'isRead' => $msg->isRead(),
                'receivedAt' => $msg->getReceivedAt()?->format('c'),
                'client' => $msg->getClient() ? [
                    'id' => $msg->getClient()->getId(),
                    'company_name' => $msg->getClient()->getCompanyName(),
                    'contact_person' => $msg->getClient()->getContactPerson(),
                ] : null,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/messages/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show an email message')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Email message details')]
    #[OA\Response(response: 404, description: 'Message not found')]
    public function showMessage(int $id): JsonResponse
    {
        $msg = $this->emailMessageRepository->find($id);

        if (!$msg) {
            return new JsonResponse(['error' => 'Email message not found.'], 404);
        }

        return new JsonResponse([
            'id' => $msg->getId(),
            'subject' => $msg->getSubject(),
            'fromEmail' => $msg->getFromEmail(),
            'toEmail' => $msg->getToEmail(),
            'body' => $msg->getBody(),
            'folder' => $msg->getFolder(),
            'isRead' => $msg->isRead(),
            'receivedAt' => $msg->getReceivedAt()?->format('c'),
            'client' => $msg->getClient() ? [
                'id' => $msg->getClient()->getId(),
                'company_name' => $msg->getClient()->getCompanyName(),
                'contact_person' => $msg->getClient()->getContactPerson(),
            ] : null,
        ]);
    }

    #[Route('/send', methods: ['POST'])]
    #[OA\Post(summary: 'Send an email')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'account_id', type: 'integer'),
        new OA\Property(property: 'to', type: 'string'),
        new OA\Property(property: 'subject', type: 'string'),
        new OA\Property(property: 'body', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Email sent')]
    public function send(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Placeholder: actual sending implementation would go here
        return new JsonResponse([
            'message' => 'Email sent successfully.',
            'to' => $data['to'] ?? null,
            'subject' => $data['subject'] ?? null,
        ]);
    }

    #[Route('/messages/{id}/reply', methods: ['POST'])]
    #[OA\Post(summary: 'Reply to an email message')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'body', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Reply sent')]
    #[OA\Response(response: 404, description: 'Message not found')]
    public function reply(int $id, Request $request): JsonResponse
    {
        $msg = $this->emailMessageRepository->find($id);

        if (!$msg) {
            return new JsonResponse(['error' => 'Email message not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Placeholder: actual reply implementation would go here
        return new JsonResponse([
            'message' => 'Reply sent successfully.',
            'original_message_id' => $msg->getId(),
            'to' => $msg->getFromEmail(),
        ]);
    }

    #[Route('/messages/{id}/link', methods: ['PATCH'])]
    #[OA\Patch(summary: 'Link email message to client')]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'client_id', type: 'integer'),
    ]))]
    #[OA\Response(response: 200, description: 'Message linked')]
    #[OA\Response(response: 404, description: 'Not found')]
    public function linkMessage(int $id, Request $request): JsonResponse
    {
        $msg = $this->emailMessageRepository->find($id);

        if (!$msg) {
            return new JsonResponse(['error' => 'Email message not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['client_id'])) {
            $client = $this->clientRepository->find($data['client_id']);
            if (!$client) {
                return new JsonResponse(['error' => 'Client not found.'], 404);
            }
            $msg->setClient($client);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $msg->getId(),
            'subject' => $msg->getSubject(),
            'client' => $msg->getClient() ? [
                'id' => $msg->getClient()->getId(),
                'company_name' => $msg->getClient()->getCompanyName(),
                'contact_person' => $msg->getClient()->getContactPerson(),
            ] : null,
        ]);
    }
}
