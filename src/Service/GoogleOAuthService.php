<?php

namespace App\Service;

use App\Entity\EmailAccount;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleOAuthService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';

    private const SCOPES = [
        'https://mail.google.com/',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EncryptionService $encryptionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly string $googleClientId,
        private readonly string $googleClientSecret,
        private readonly string $googleRedirectUri,
    ) {}

    public function getAuthorizationUrl(?string $state = null): string
    {
        $params = [
            'client_id' => $this->googleClientId,
            'redirect_uri' => $this->googleRedirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code): array
    {
        $response = $this->httpClient->request('POST', self::TOKEN_URL, [
            'body' => [
                'code' => $code,
                'client_id' => $this->googleClientId,
                'client_secret' => $this->googleClientSecret,
                'redirect_uri' => $this->googleRedirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        $data = $response->toArray();

        if (!isset($data['access_token'])) {
            throw new \RuntimeException('Google OAuth: no access_token in response');
        }

        return $data;
    }

    public function refreshAccessToken(string $refreshTokenEncrypted): array
    {
        $refreshToken = $this->encryptionService->decrypt($refreshTokenEncrypted);

        $response = $this->httpClient->request('POST', self::TOKEN_URL, [
            'body' => [
                'refresh_token' => $refreshToken,
                'client_id' => $this->googleClientId,
                'client_secret' => $this->googleClientSecret,
                'grant_type' => 'refresh_token',
            ],
        ]);

        return $response->toArray();
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', self::USERINFO_URL, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);

        return $response->toArray();
    }

    public function createOrUpdateAccountFromOAuth(string $code): EmailAccount
    {
        $tokens = $this->exchangeCodeForTokens($code);
        $userInfo = $this->getUserInfo($tokens['access_token']);

        $email = $userInfo['email'] ?? null;
        if (!$email) {
            throw new \RuntimeException('Could not retrieve email from Google');
        }

        $account = $this->entityManager->getRepository(EmailAccount::class)
            ->findOneBy(['emailAddress' => $email]);

        if (!$account) {
            $account = new EmailAccount();
            $account->setEmailAddress($email);
            $account->setImapHost('imap.gmail.com');
            $account->setImapPort(993);
            $account->setImapEncryption('ssl');
            $account->setSmtpHost('smtp.gmail.com');
            $account->setSmtpPort(465);
            $account->setSmtpEncryption('ssl');
        }

        $account->setDisplayName($userInfo['name'] ?? $email);
        $account->setAuthType(EmailAccount::AUTH_OAUTH2);
        $account->setProvider('google');
        $account->setUsername($email);
        $account->setOauthAccessTokenEncrypted(
            $this->encryptionService->encrypt($tokens['access_token'])
        );

        if (isset($tokens['refresh_token'])) {
            $account->setOauthRefreshTokenEncrypted(
                $this->encryptionService->encrypt($tokens['refresh_token'])
            );
        }

        $expiresIn = $tokens['expires_in'] ?? 3600;
        $account->setOauthTokenExpiresAt(
            new \DateTimeImmutable("+{$expiresIn} seconds")
        );

        $account->setIsActive(true);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->logger->info('Google OAuth account configured', ['email' => $email]);

        return $account;
    }

    public function getValidAccessToken(EmailAccount $account): string
    {
        if (!$account->isOAuth()) {
            throw new \RuntimeException('Account is not OAuth');
        }

        $expiresAt = $account->getOauthTokenExpiresAt();
        if ($expiresAt && $expiresAt > new \DateTimeImmutable('+60 seconds')) {
            return $this->encryptionService->decrypt($account->getOauthAccessTokenEncrypted());
        }

        $refreshTokenEncrypted = $account->getOauthRefreshTokenEncrypted();
        if (!$refreshTokenEncrypted) {
            throw new \RuntimeException('No refresh token available, re-authorization needed');
        }

        $tokens = $this->refreshAccessToken($refreshTokenEncrypted);

        $account->setOauthAccessTokenEncrypted(
            $this->encryptionService->encrypt($tokens['access_token'])
        );
        $expiresIn = $tokens['expires_in'] ?? 3600;
        $account->setOauthTokenExpiresAt(
            new \DateTimeImmutable("+{$expiresIn} seconds")
        );

        if (isset($tokens['refresh_token'])) {
            $account->setOauthRefreshTokenEncrypted(
                $this->encryptionService->encrypt($tokens['refresh_token'])
            );
        }

        $this->entityManager->flush();

        return $tokens['access_token'];
    }

    public function isConfigured(): bool
    {
        return !empty($this->googleClientId) && !empty($this->googleClientSecret);
    }
}
