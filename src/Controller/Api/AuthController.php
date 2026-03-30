<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\OAuthTokenService;
use App\Service\TwoFactorService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    public function __construct(
        private OAuthTokenService $oAuthTokenService,
        private TwoFactorService $twoFactorService,
    ) {}

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Handled by json_login authenticator — this is never reached directly.
        // The AuthenticationSuccessHandler checks 2FA and may return requires_2fa.
        return new JsonResponse(['error' => 'Missing json_login configuration'], 500);
    }

    #[Route('/2fa/verify', name: 'api_auth_2fa_verify', methods: ['POST'])]
    public function verify2fa(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        if (!$this->twoFactorService->verifyCode($user, $code)) {
            return new JsonResponse(['error' => 'Nieprawidlowy lub wygasly kod.'], 400);
        }

        $request->getSession()->set('2fa_verified', true);

        return new JsonResponse([
            'message' => '2FA verified.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }

    #[Route('/2fa/resend', name: 'api_auth_2fa_resend', methods: ['POST'])]
    public function resend2fa(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated.'], 401);
        }

        $sent = $this->twoFactorService->generateAndSendCode($user);

        return new JsonResponse([
            'message' => $sent ? 'Kod wyslany ponownie.' : 'Nie udalo sie wyslac kodu SMS.',
            'sent' => $sent,
        ]);
    }

    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('Handled by Symfony logout listener.');
    }

    #[Route('/logout-success', name: 'api_auth_logout_success', methods: ['GET', 'POST'])]
    public function logoutSuccess(): JsonResponse
    {
        return new JsonResponse(['message' => 'Logged out successfully.']);
    }

    #[Route('/me', methods: ['GET'])]
    #[OA\Get(summary: 'Get current authenticated user')]
    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated.'], 401);
        }

        // If 2FA enabled but not yet verified in this session
        if ($user->isTwoFactorEnabled() && !$request->getSession()->get('2fa_verified', false)) {
            return new JsonResponse(['error' => '2FA verification required.', 'requires_2fa' => true], 403);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'twoFactorEnabled' => $user->isTwoFactorEnabled(),
        ]);
    }

    #[Route('/token', methods: ['POST'])]
    #[OA\Post(summary: 'Generate OAuth token')]
    public function token(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated.'], 401);
        }

        $token = $this->oAuthTokenService->generateToken($user);

        return new JsonResponse([
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_at' => $token->getExpiresAt()->format('c'),
        ]);
    }

    #[Route('/token/refresh', methods: ['POST'])]
    #[OA\Post(summary: 'Refresh OAuth token')]
    public function refreshToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['refresh_token'])) {
            return new JsonResponse(['error' => 'Refresh token is required.'], 400);
        }

        $token = $this->oAuthTokenService->refreshToken($data['refresh_token']);
        if (!$token) {
            return new JsonResponse(['error' => 'Invalid or expired refresh token.'], 400);
        }

        return new JsonResponse([
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_at' => $token->getExpiresAt()->format('c'),
        ]);
    }

    #[Route('/token/revoke', methods: ['POST'])]
    #[OA\Post(summary: 'Revoke OAuth token')]
    public function revokeToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['token'])) {
            return new JsonResponse(['error' => 'Token is required.'], 400);
        }

        $this->oAuthTokenService->revokeToken($data['token']);
        return new JsonResponse(['message' => 'Token revoked.']);
    }
}
