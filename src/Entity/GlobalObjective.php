<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'global_objective')]
class GlobalObjective
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['global_objective:read', 'global_objective:write'])]
        private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['global_objective:read', 'global_objective:write'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['global_objective:read', 'global_objective:write'])]
    private string $objectiveDescription;

    #[ORM\Column(type: 'string', enumType: ObjectiveType::class)]
    #[Groups(['global_objective:read', 'global_objective:write'])]
    private ObjectiveType $type;

    #[ORM\OneToOne(targetEntity: Year::class, inversedBy: 'globalObjective', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'year_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?Year $year = null;

    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'globalObjective', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['global_objective:read'])]
    private Collection $goals;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
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

    public function getObjectiveDescription(): string
    {
        return $this->objectiveDescription;
    }

    public function setObjectiveDescription(string $objectiveDescription): self
    {
        $this->objectiveDescription = $objectiveDescription;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->setGlobalObjective($this);
        }
        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal)) {
            if ($goal->getGlobalObjective() === $this) {
                $goal->setGlobalObjective(null);
            }
        }
        return $this;
    }

    public function getOverallProgress(): float
    {
        if ($this->goals->isEmpty()) {
            return 0;
        }

        $totalProgress = 0;
        foreach ($this->goals as $goal) {
            $totalProgress += $goal->getProgressPercentage();
        }

        return $totalProgress / $this->goals->count();
    }

    public function getCompletedGoalsCount(): int
    {
        return $this->goals->filter(fn(Goal $goal) => $goal->isCompleted())->count();
    }

    public function getTotalGoalsCount(): int
    {
        return $this->goals->count();
    }
}

