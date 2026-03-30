<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MeetingParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Meeting::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meeting $meeting = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalEmail = null;

    public function getId(): ?int { return $this->id; }
    public function getMeeting(): ?Meeting { return $this->meeting; }
    public function setMeeting(?Meeting $meeting): static { $this->meeting = $meeting; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getExternalName(): ?string { return $this->externalName; }
    public function setExternalName(?string $externalName): static { $this->externalName = $externalName; return $this; }
    public function getExternalEmail(): ?string { return $this->externalEmail; }
    public function setExternalEmail(?string $externalEmail): static { $this->externalEmail = $externalEmail; return $this; }
}
