<?php

namespace App\Service;

use App\Entity\OAuthToken;
use App\Entity\User;
use App\Repository\OAuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class OAuthTokenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OAuthTokenRepository $oAuthTokenRepository,
    ) {
    }

    /**
     * @param array<string> $scopes
     */
    public function generateToken(User $user, array $scopes = []): OAuthToken
    {
        $token = new OAuthToken();
        $token->setUser($user);
        $token->setToken(bin2hex(random_bytes(32)));
        $token->setRefreshToken(bin2hex(random_bytes(32)));
        $token->setScopes($scopes);
        $token->setExpiresAt(new \DateTimeImmutable('+24 hours'));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function refreshToken(string $refreshToken): ?OAuthToken
    {
        $existingToken = $this->oAuthTokenRepository->findOneBy(['refreshToken' => $refreshToken]);

        if ($existingToken === null) {
            return null;
        }

        $user = $existingToken->getUser();
        $scopes = $existingToken->getScopes();

        $this->revokeToken($existingToken->getToken());

        return $this->generateToken($user, $scopes);
    }

    public function revokeToken(string $token): void
    {
        $oAuthToken = $this->oAuthTokenRepository->findOneBy(['token' => $token]);

        if ($oAuthToken === null) {
            return;
        }

        $oAuthToken->setRevokedAt(new \DateTimeImmutable());

        $this->entityManager->persist($oAuthToken);
        $this->entityManager->flush();
    }

    public function validateToken(string $token): ?OAuthToken
    {
        return $this->oAuthTokenRepository->findValidToken($token);
    }
}
