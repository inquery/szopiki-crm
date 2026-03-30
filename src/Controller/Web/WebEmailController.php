<?php

namespace App\Controller\Web;

use App\Repository\EmailAccountRepository;
use App\Repository\EmailMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebEmailController extends AbstractController
{
    public function __construct(
        private EmailAccountRepository $emailAccountRepository,
        private EmailMessageRepository $emailMessageRepository,
    ) {}

    #[Route('/emails', name: 'email_inbox')]
    public function inbox(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $accounts = $this->emailAccountRepository->findAll();
        $messages = $this->emailMessageRepository->findBy([], ['receivedAt' => 'DESC'], 50);

        return $this->render('email/inbox.html.twig', [
            'accounts' => $accounts,
            'messages' => $messages,
        ]);
    }
}
