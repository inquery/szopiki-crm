<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TwoFactorService
{
    public function __construct(
        private readonly SmsService $smsService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function generateAndSendCode(User $user): bool
    {
        if (!$user->getPhone()) {
            return false;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = new \DateTimeImmutable('+5 minutes');

        $user->setTwoFactorCode(password_hash($code, PASSWORD_DEFAULT));
        $user->setTwoFactorCodeExpiresAt($expiresAt);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->smsService->send(
            $user->getPhone(),
            "Twoj kod weryfikacyjny CRM: {$code}. Wazny 5 minut."
        );
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->getTwoFactorCode() || !$user->getTwoFactorCodeExpiresAt()) {
            return false;
        }

        if ($user->getTwoFactorCodeExpiresAt() < new \DateTimeImmutable()) {
            $this->clearCode($user);
            return false;
        }

        if (!password_verify($code, $user->getTwoFactorCode())) {
            return false;
        }

        $this->clearCode($user);
        return true;
    }

    private function clearCode(User $user): void
    {
        $user->setTwoFactorCode(null);
        $user->setTwoFactorCodeExpiresAt(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
