<?php

namespace App\Entity;

use App\Repository\BattleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BattleRepository::class)]
class Battle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id')]
    private ?User $challenger = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id')]
    private ?User $opponent = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, referencedColumnName: 'id')]
    private ?User $winner = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null; // pending, in_progress, completed, cancelled

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $battleLog = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'pending';
        $this->battleLog = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChallenger(): ?User
    {
        return $this->challenger;
    }

    public function setChallenger(?User $challenger): static
    {
        $this->challenger = $challenger;
        return $this;
    }

    public function getOpponent(): ?User
    {
        return $this->opponent;
    }

    public function setOpponent(?User $opponent): static
    {
        $this->opponent = $opponent;
        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): static
    {
        $this->winner = $winner;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getBattleLog(): ?array
    {
        return $this->battleLog;
    }

    public function setBattleLog(?array $battleLog): static
    {
        $this->battleLog = $battleLog;
        return $this;
    }

    public function addLogEntry(string $entry): static
    {
        $this->battleLog[] = [
            'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'message' => $entry
        ];
        return $this;
    }
}
