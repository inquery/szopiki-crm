<?php

namespace App\Entity;

use App\Repository\PanelConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanelConfigRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PanelConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $panelType = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $panelUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $panelUsernameEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $panelPasswordEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $databaseHostEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $databaseNameEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $databaseUsernameEncrypted = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $databasePasswordEncrypted = null;

    #[ORM\Column]
    private bool $isInstalled = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $installedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $installedBy = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'panelConfigs')]
    private ?Client $client = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getPanelType(): ?string { return $this->panelType; }
    public function setPanelType(string $panelType): static { $this->panelType = $panelType; return $this; }
    public function getPanelUrl(): ?string { return $this->panelUrl; }
    public function setPanelUrl(?string $panelUrl): static { $this->panelUrl = $panelUrl; return $this; }
    public function getPanelUsernameEncrypted(): ?string { return $this->panelUsernameEncrypted; }
    public function setPanelUsernameEncrypted(?string $v): static { $this->panelUsernameEncrypted = $v; return $this; }
    public function getPanelPasswordEncrypted(): ?string { return $this->panelPasswordEncrypted; }
    public function setPanelPasswordEncrypted(?string $v): static { $this->panelPasswordEncrypted = $v; return $this; }
    public function getDatabaseHostEncrypted(): ?string { return $this->databaseHostEncrypted; }
    public function setDatabaseHostEncrypted(?string $v): static { $this->databaseHostEncrypted = $v; return $this; }
    public function getDatabaseNameEncrypted(): ?string { return $this->databaseNameEncrypted; }
    public function setDatabaseNameEncrypted(?string $v): static { $this->databaseNameEncrypted = $v; return $this; }
    public function getDatabaseUsernameEncrypted(): ?string { return $this->databaseUsernameEncrypted; }
    public function setDatabaseUsernameEncrypted(?string $v): static { $this->databaseUsernameEncrypted = $v; return $this; }
    public function getDatabasePasswordEncrypted(): ?string { return $this->databasePasswordEncrypted; }
    public function setDatabasePasswordEncrypted(?string $v): static { $this->databasePasswordEncrypted = $v; return $this; }
    public function isInstalled(): bool { return $this->isInstalled; }
    public function setIsInstalled(bool $isInstalled): static { $this->isInstalled = $isInstalled; return $this; }
    public function getInstalledAt(): ?\DateTimeImmutable { return $this->installedAt; }
    public function setInstalledAt(?\DateTimeImmutable $installedAt): static { $this->installedAt = $installedAt; return $this; }
    public function getInstalledBy(): ?User { return $this->installedBy; }
    public function setInstalledBy(?User $installedBy): static { $this->installedBy = $installedBy; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
