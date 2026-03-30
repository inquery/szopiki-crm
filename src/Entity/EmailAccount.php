<?php

namespace App\Entity;

use App\Repository\EmailAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmailAccountRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailAccount
{
    public const AUTH_PASSWORD = 'password';
    public const AUTH_OAUTH2 = 'oauth2';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $emailAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 20)]
    private string $authType = self::AUTH_PASSWORD;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imapHost = null;

    #[ORM\Column]
    private int $imapPort = 993;

    #[ORM\Column(length: 10)]
    private string $imapEncryption = 'ssl';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smtpHost = null;

    #[ORM\Column]
    private int $smtpPort = 465;

    #[ORM\Column(length: 10)]
    private string $smtpEncryption = 'ssl';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $passwordEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oauthAccessTokenEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oauthRefreshTokenEncrypted = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $oauthTokenExpiresAt = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSyncAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: EmailMessage::class, mappedBy: 'emailAccount')]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isOAuth(): bool { return $this->authType === self::AUTH_OAUTH2; }

    public function getId(): ?int { return $this->id; }
    public function getEmailAddress(): ?string { return $this->emailAddress; }
    public function setEmailAddress(string $emailAddress): static { $this->emailAddress = $emailAddress; return $this; }
    public function getDisplayName(): ?string { return $this->displayName; }
    public function setDisplayName(?string $displayName): static { $this->displayName = $displayName; return $this; }
    public function getAuthType(): string { return $this->authType; }
    public function setAuthType(string $authType): static { $this->authType = $authType; return $this; }
    public function getProvider(): ?string { return $this->provider; }
    public function setProvider(?string $provider): static { $this->provider = $provider; return $this; }
    public function getImapHost(): ?string { return $this->imapHost; }
    public function setImapHost(?string $imapHost): static { $this->imapHost = $imapHost; return $this; }
    public function getImapPort(): int { return $this->imapPort; }
    public function setImapPort(int $imapPort): static { $this->imapPort = $imapPort; return $this; }
    public function getImapEncryption(): string { return $this->imapEncryption; }
    public function setImapEncryption(string $imapEncryption): static { $this->imapEncryption = $imapEncryption; return $this; }
    public function getSmtpHost(): ?string { return $this->smtpHost; }
    public function setSmtpHost(?string $smtpHost): static { $this->smtpHost = $smtpHost; return $this; }
    public function getSmtpPort(): int { return $this->smtpPort; }
    public function setSmtpPort(int $smtpPort): static { $this->smtpPort = $smtpPort; return $this; }
    public function getSmtpEncryption(): string { return $this->smtpEncryption; }
    public function setSmtpEncryption(string $smtpEncryption): static { $this->smtpEncryption = $smtpEncryption; return $this; }
    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): static { $this->username = $username; return $this; }
    public function getPasswordEncrypted(): ?string { return $this->passwordEncrypted; }
    public function setPasswordEncrypted(?string $passwordEncrypted): static { $this->passwordEncrypted = $passwordEncrypted; return $this; }
    public function getOauthAccessTokenEncrypted(): ?string { return $this->oauthAccessTokenEncrypted; }
    public function setOauthAccessTokenEncrypted(?string $v): static { $this->oauthAccessTokenEncrypted = $v; return $this; }
    public function getOauthRefreshTokenEncrypted(): ?string { return $this->oauthRefreshTokenEncrypted; }
    public function setOauthRefreshTokenEncrypted(?string $v): static { $this->oauthRefreshTokenEncrypted = $v; return $this; }
    public function getOauthTokenExpiresAt(): ?\DateTimeImmutable { return $this->oauthTokenExpiresAt; }
    public function setOauthTokenExpiresAt(?\DateTimeImmutable $v): static { $this->oauthTokenExpiresAt = $v; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function getLastSyncAt(): ?\DateTimeImmutable { return $this->lastSyncAt; }
    public function setLastSyncAt(?\DateTimeImmutable $lastSyncAt): static { $this->lastSyncAt = $lastSyncAt; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getMessages(): Collection { return $this->messages; }
}
