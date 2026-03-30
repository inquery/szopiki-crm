<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Client
{
    public const STATUS_PROSPECT = 'prospect';
    public const STATUS_DEMO = 'demo';
    public const STATUS_IMPLEMENTING = 'implementing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RESIGNED = 'resigned';
    public const STATUS_DELETED = 'deleted';

    public const STATUSES = [
        self::STATUS_PROSPECT,
        self::STATUS_DEMO,
        self::STATUS_IMPLEMENTING,
        self::STATUS_ACTIVE,
        self::STATUS_RESIGNED,
        self::STATUS_DELETED,
    ];

    public const TRANSITIONS = [
        self::STATUS_PROSPECT     => [self::STATUS_DEMO, self::STATUS_RESIGNED],
        self::STATUS_DEMO         => [self::STATUS_IMPLEMENTING, self::STATUS_ACTIVE, self::STATUS_RESIGNED],
        self::STATUS_IMPLEMENTING => [self::STATUS_ACTIVE, self::STATUS_RESIGNED],
        self::STATUS_ACTIVE       => [self::STATUS_RESIGNED],
        self::STATUS_RESIGNED     => [self::STATUS_DEMO, self::STATUS_IMPLEMENTING, self::STATUS_ACTIVE],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $companyName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $taxId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPerson = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    private string $country = 'Polska';

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PROSPECT;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resignedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'clients')]
    private ?User $assignedUser = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Deal::class, mappedBy: 'client')]
    private Collection $deals;

    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'client')]
    private Collection $noteEntries;

    #[ORM\OneToMany(targetEntity: Meeting::class, mappedBy: 'client')]
    private Collection $meetings;

    #[ORM\OneToMany(targetEntity: PanelConfig::class, mappedBy: 'client')]
    private Collection $panelConfigs;

    public function __construct()
    {
        $this->deals = new ArrayCollection();
        $this->noteEntries = new ArrayCollection();
        $this->meetings = new ArrayCollection();
        $this->panelConfigs = new ArrayCollection();
    }

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
    public function getCompanyName(): ?string { return $this->companyName; }
    public function setCompanyName(string $companyName): static { $this->companyName = $companyName; return $this; }
    public function getTaxId(): ?string { return $this->taxId; }
    public function setTaxId(?string $taxId): static { $this->taxId = $taxId; return $this; }
    public function getContactPerson(): ?string { return $this->contactPerson; }
    public function setContactPerson(?string $contactPerson): static { $this->contactPerson = $contactPerson; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): static { $this->phone = $phone; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): static { $this->address = $address; return $this; }
    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): static { $this->city = $city; return $this; }
    public function getPostalCode(): ?string { return $this->postalCode; }
    public function setPostalCode(?string $postalCode): static { $this->postalCode = $postalCode; return $this; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): static { $this->country = $country; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getSource(): ?string { return $this->source; }
    public function setSource(?string $source): static { $this->source = $source; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }
    public function getResignedAt(): ?\DateTimeImmutable { return $this->resignedAt; }
    public function setResignedAt(?\DateTimeImmutable $resignedAt): static { $this->resignedAt = $resignedAt; return $this; }
    public function getAssignedUser(): ?User { return $this->assignedUser; }
    public function setAssignedUser(?User $assignedUser): static { $this->assignedUser = $assignedUser; return $this; }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    public function getDeletionDate(): ?\DateTimeImmutable
    {
        if ($this->status !== self::STATUS_RESIGNED || !$this->resignedAt) {
            return null;
        }
        return $this->resignedAt->modify('+30 days');
    }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getDeals(): Collection { return $this->deals; }
    public function getNoteEntries(): Collection { return $this->noteEntries; }
    public function getMeetings(): Collection { return $this->meetings; }
    public function getPanelConfigs(): Collection { return $this->panelConfigs; }
}
