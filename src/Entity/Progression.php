<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'progression')]
class Progression
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['progression:read', 'progression:write'])]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['progression:read', 'progression:write'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['progression:read', 'progression:write'])]
    private ?string $progressionDescription = null;

    #[ORM\Column(type: 'float')]
    #[Groups(['progression:read', 'progression:write'])]
    private float $measure;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['progression:read', 'progression:write'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Goal::class, inversedBy: 'progressions')]
    #[ORM\JoinColumn(name: 'goal_id', referencedColumnName: 'id')]
    #[Groups(['progression:read'])]
    private ?Goal $goal = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getProgressionDescription(): ?string
    {
        return $this->progressionDescription;
    }

    public function setProgressionDescription(?string $progressionDescription): self
    {
        $this->progressionDescription = $progressionDescription;
        return $this;
    }

    public function getMeasure(): float
    {
        return $this->measure;
    }

    public function setMeasure(float $measure): self
    {
        $this->measure = $measure;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): self
    {
        $this->goal = $goal;
        return $this;
    }
}

