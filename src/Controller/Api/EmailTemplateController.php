<?php

namespace App\Controller\Api;

use App\Entity\EmailTemplate;
use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/emails/templates')]
#[OA\Tag(name: 'Email Templates')]
class EmailTemplateController extends AbstractController
{
    public function __construct(
        private EmailTemplateRepository $templateRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    private function serialize(EmailTemplate $t): array
    {
        return [
            'id' => $t->getId(),
            'name' => $t->getName(),
            'subject' => $t->getSubject(),
            'body_html' => $t->getBodyHtml(),
            'description' => $t->getDescription(),
            'is_active' => $t->isActive(),
            'created_at' => $t->getCreatedAt()?->format('c'),
            'updated_at' => $t->getUpdatedAt()?->format('c'),
        ];
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(summary: 'List email templates')]
    public function index(): JsonResponse
    {
        $templates = $this->templateRepository->findBy([], ['name' => 'ASC']);
        return new JsonResponse(array_map(fn($t) => $this->serialize($t), $templates));
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create email template')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $template = new EmailTemplate();
        $template->setName($data['name'] ?? '');
        $template->setSubject($data['subject'] ?? '');
        $template->setBodyHtml($data['body_html'] ?? '');
        $template->setDescription($data['description'] ?? null);
        $template->setIsActive($data['is_active'] ?? true);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return new JsonResponse($this->serialize($template), 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Show email template')]
    public function show(int $id): JsonResponse
    {
        $template = $this->templateRepository->find($id);
        if (!$template) return new JsonResponse(['error' => 'Template not found.'], 404);
        return new JsonResponse($this->serialize($template));
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Update email template')]
    public function update(int $id, Request $request): JsonResponse
    {
        $template = $this->templateRepository->find($id);
        if (!$template) return new JsonResponse(['error' => 'Template not found.'], 404);

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) $template->setName($data['name']);
        if (isset($data['subject'])) $template->setSubject($data['subject']);
        if (isset($data['body_html'])) $template->setBodyHtml($data['body_html']);
        if (array_key_exists('description', $data)) $template->setDescription($data['description']);
        if (isset($data['is_active'])) $template->setIsActive((bool) $data['is_active']);

        $this->entityManager->flush();

        return new JsonResponse($this->serialize($template));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete email template')]
    public function delete(int $id): JsonResponse
    {
        $template = $this->templateRepository->find($id);
        if (!$template) return new JsonResponse(['error' => 'Template not found.'], 404);

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }
}
