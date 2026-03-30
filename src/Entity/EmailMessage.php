<?php

namespace App\Entity;

use App\Repository\EmailMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EmailAccount::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailAccount $emailAccount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $messageId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $inReplyTo = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    private ?string $fromAddress = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $toAddresses = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ccAddresses = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bodyText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(length: 10)]
    private ?string $direction = null;

    #[ORM\Column(length: 100)]
    private string $folder = 'INBOX';

    #[ORM\Column]
    private bool $isRead = false;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    private ?Client $client = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $receivedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: EmailAttachment::class, mappedBy: 'emailMessage', cascade: ['persist', 'remove'])]
    private Collection $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getEmailAccount(): ?EmailAccount { return $this->emailAccount; }
    public function setEmailAccount(?EmailAccount $emailAccount): static { $this->emailAccount = $emailAccount; return $this; }
    public function getMessageId(): ?string { return $this->messageId; }
    public function setMessageId(?string $messageId): static { $this->messageId = $messageId; return $this; }
    public function getInReplyTo(): ?string { return $this->inReplyTo; }
    public function setInReplyTo(?string $inReplyTo): static { $this->inReplyTo = $inReplyTo; return $this; }
    public function getSubject(): ?string { return $this->subject; }
    public function setSubject(?string $subject): static { $this->subject = $subject; return $this; }
    public function getFromAddress(): ?string { return $this->fromAddress; }
    public function setFromAddress(string $fromAddress): static { $this->fromAddress = $fromAddress; return $this; }
    public function getToAddresses(): ?array { return $this->toAddresses; }
    public function setToAddresses(?array $toAddresses): static { $this->toAddresses = $toAddresses; return $this; }
    public function getCcAddresses(): ?array { return $this->ccAddresses; }
    public function setCcAddresses(?array $ccAddresses): static { $this->ccAddresses = $ccAddresses; return $this; }
    public function getBodyText(): ?string { return $this->bodyText; }
    public function setBodyText(?string $bodyText): static { $this->bodyText = $bodyText; return $this; }
    public function getBodyHtml(): ?string { return $this->bodyHtml; }
    public function setBodyHtml(?string $bodyHtml): static { $this->bodyHtml = $bodyHtml; return $this; }
    public function getDirection(): ?string { return $this->direction; }
    public function setDirection(string $direction): static { $this->direction = $direction; return $this; }
    public function getFolder(): string { return $this->folder; }
    public function setFolder(string $folder): static { $this->folder = $folder; return $this; }
    public function isRead(): bool { return $this->isRead; }
    public function setIsRead(bool $isRead): static { $this->isRead = $isRead; return $this; }
    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getReceivedAt(): ?\DateTimeImmutable { return $this->receivedAt; }
    public function setReceivedAt(?\DateTimeImmutable $receivedAt): static { $this->receivedAt = $receivedAt; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getAttachments(): Collection { return $this->attachments; }
}
