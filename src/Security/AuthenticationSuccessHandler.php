<?php

namespace App\Security;

use App\Entity\User;
use App\Service\TwoFactorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private TwoFactorService $twoFactorService,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        if ($user->isTwoFactorEnabled() && $user->getPhone()) {
            $this->twoFactorService->generateAndSendCode($user);

            return new JsonResponse([
                'message' => 'Login successful. 2FA code sent.',
                'requires_2fa' => true,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                ],
            ]);
        }

        $request->getSession()->set('2fa_verified', true);

        return new JsonResponse([
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }
}
