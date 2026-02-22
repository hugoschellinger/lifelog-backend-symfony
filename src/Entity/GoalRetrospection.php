<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'goal_retrospection')]
class GoalRetrospection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['goal_retrospection:read', 'goal_retrospection:write', 'goal:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['goal_retrospection:read', 'goal_retrospection:write', 'goal:read'])]
    private string $bilan;

    #[ORM\ManyToOne(targetEntity: Goal::class, inversedBy: 'goalRetrospections')]
    #[ORM\JoinColumn(name: 'goal_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?Goal $goal = null;

    #[ORM\ManyToOne(targetEntity: Retrospection::class, inversedBy: 'goalRetrospections')]
    #[ORM\JoinColumn(name: 'retrospection_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?Retrospection $retrospection = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBilan(): string
    {
        return $this->bilan;
    }

    public function setBilan(string $bilan): self
    {
        $this->bilan = $bilan;
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

    public function getRetrospection(): ?Retrospection
    {
        return $this->retrospection;
    }

    public function setRetrospection(?Retrospection $retrospection): self
    {
        $this->retrospection = $retrospection;
        return $this;
    }
    
    #[Groups(['goal_retrospection:read'])]
    public function getGoalId(): ?int
    {
        return $this->goal?->getId();
    }
}

