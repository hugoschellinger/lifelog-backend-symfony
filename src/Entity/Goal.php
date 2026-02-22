<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'goal')]
class Goal
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['goal:read', 'goal:write'])]
        private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['goal:read', 'goal:write'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['goal:read', 'goal:write'])]
    private ?string $goalDescription = null;

    #[ORM\Column(type: 'float')]
    #[Groups(['goal:read', 'goal:write'])]
    private float $measure;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['goal:read', 'goal:write'])]
    private string $measureLabel;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['goal:read', 'goal:write'])]
    private \DateTimeInterface $targetDate;

    #[ORM\Column(type: 'string', enumType: ObjectiveType::class)]
    #[Groups(['goal:read', 'goal:write'])]
    private ObjectiveType $type;

    #[ORM\ManyToOne(targetEntity: GlobalObjective::class, inversedBy: 'goals')]
    #[ORM\JoinColumn(name: 'global_objective_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?GlobalObjective $globalObjective = null;

    #[ORM\OneToMany(targetEntity: Progression::class, mappedBy: 'goal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    #[Groups(['goal:read'])]
    private Collection $progressions;

    #[ORM\OneToMany(targetEntity: GoalRetrospection::class, mappedBy: 'goal', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['goal:read'])]
    private Collection $goalRetrospections;

    public function __construct()
    {
        $this->progressions = new ArrayCollection();
        $this->goalRetrospections = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getGoalDescription(): ?string
    {
        return $this->goalDescription;
    }

    public function setGoalDescription(?string $goalDescription): self
    {
        $this->goalDescription = $goalDescription;
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

    public function getMeasureLabel(): string
    {
        return $this->measureLabel;
    }

    public function setMeasureLabel(string $measureLabel): self
    {
        $this->measureLabel = $measureLabel;
        return $this;
    }

    public function getTargetDate(): \DateTimeInterface
    {
        return $this->targetDate;
    }

    public function setTargetDate(\DateTimeInterface $targetDate): self
    {
        $this->targetDate = $targetDate;
        return $this;
    }

    public function getType(): ObjectiveType
    {
        return $this->type;
    }

    public function setType(ObjectiveType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getGlobalObjective(): ?GlobalObjective
    {
        return $this->globalObjective;
    }

    public function setGlobalObjective(?GlobalObjective $globalObjective): self
    {
        $this->globalObjective = $globalObjective;
        return $this;
    }

    /**
     * @return Collection<int, Progression>
     */
    public function getProgressions(): Collection
    {
        return $this->progressions;
    }

    public function addProgression(Progression $progression): self
    {
        if (!$this->progressions->contains($progression)) {
            $this->progressions->add($progression);
            $progression->setGoal($this);
        }
        return $this;
    }

    public function removeProgression(Progression $progression): self
    {
        if ($this->progressions->removeElement($progression)) {
            if ($progression->getGoal() === $this) {
                $progression->setGoal(null);
            }
        }
        return $this;
    }

    /**
     * Retourne la progression actuelle (somme de toutes les progressions).
     * Chaque progression représente une valeur à ajouter au total, pas à remplacer.
     */
    public function getCurrentProgress(): float
    {
        $total = 0.0;
        foreach ($this->progressions as $progression) {
            $total += $progression->getMeasure();
        }

        return $total;
    }

    public function getProgressPercentage(): float
    {
        if ($this->measure <= 0) {
            return 0;
        }

        return min($this->getCurrentProgress() / $this->measure * 100, 100);
    }

    public function isCompleted(): bool
    {
        return $this->getCurrentProgress() >= $this->measure;
    }

    public function getDaysRemaining(): int
    {
        $now = new \DateTime();
        $diff = $now->diff($this->targetDate);
        return $diff->invert ? 0 : $diff->days;
    }

    /**
     * @return Collection<int, GoalRetrospection>
     */
    public function getGoalRetrospections(): Collection
    {
        return $this->goalRetrospections;
    }

    public function addGoalRetrospection(GoalRetrospection $goalRetrospection): self
    {
        if (!$this->goalRetrospections->contains($goalRetrospection)) {
            $this->goalRetrospections->add($goalRetrospection);
            $goalRetrospection->setGoal($this);
        }
        return $this;
    }

    public function removeGoalRetrospection(GoalRetrospection $goalRetrospection): self
    {
        if ($this->goalRetrospections->removeElement($goalRetrospection)) {
            if ($goalRetrospection->getGoal() === $this) {
                $goalRetrospection->setGoal(null);
            }
        }
        return $this;
    }
}

