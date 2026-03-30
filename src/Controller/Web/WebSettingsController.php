<?php

namespace App\Controller\Web;

use App\Repository\EmailAccountRepository;
use App\Repository\EmailTemplateRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class WebSettingsController extends AbstractController
{
    #[Route('/users', name: 'settings_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('settings/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/email-accounts', name: 'settings_email_accounts')]
    public function emailAccounts(EmailAccountRepository $emailAccountRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('settings/email_accounts.html.twig', [
            'accounts' => $emailAccountRepository->findAll(),
        ]);
    }

    #[Route('/email-templates', name: 'settings_email_templates')]
    public function emailTemplates(EmailTemplateRepository $emailTemplateRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('settings/email_templates.html.twig', [
            'templates' => $emailTemplateRepository->findAll(),
        ]);
    }
}
