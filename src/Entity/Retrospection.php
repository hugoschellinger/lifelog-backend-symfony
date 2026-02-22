<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'retrospection')]
class Retrospection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['retrospection:read', 'retrospection:write'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['retrospection:read', 'retrospection:write'])]
    private ?string $yearReflection = null;

    #[ORM\OneToOne(targetEntity: Year::class, inversedBy: 'retrospection', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'year_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?Year $year = null;

    #[ORM\OneToMany(targetEntity: GoalRetrospection::class, mappedBy: 'retrospection', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['retrospection:read'])]
    private Collection $goalRetrospections;

    public function __construct()
    {
        $this->goalRetrospections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYearReflection(): ?string
    {
        return $this->yearReflection;
    }

    public function setYearReflection(?string $yearReflection): self
    {
        $this->yearReflection = $yearReflection;
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
            $goalRetrospection->setRetrospection($this);
        }
        return $this;
    }

    public function removeGoalRetrospection(GoalRetrospection $goalRetrospection): self
    {
        if ($this->goalRetrospections->removeElement($goalRetrospection)) {
            if ($goalRetrospection->getRetrospection() === $this) {
                $goalRetrospection->setRetrospection(null);
            }
        }
        return $this;
    }
}

