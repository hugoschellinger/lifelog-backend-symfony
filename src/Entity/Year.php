<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'year')]
#[ORM\UniqueConstraint(name: 'unique_value', columns: ['value'])]
class Year
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['year:read', 'year:write'])]
    private ?string $id = null;

    #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['year:read', 'year:write'])]
    private int $value;

    #[ORM\OneToOne(targetEntity: GlobalObjective::class, mappedBy: 'year', cascade: ['persist', 'remove'])]
    #[Groups(['year:read'])]
    private ?GlobalObjective $globalObjective = null;

    #[ORM\OneToOne(targetEntity: Questionnaire::class, mappedBy: 'year', cascade: ['persist', 'remove'])]
    #[Groups(['year:read'])]
    private ?Questionnaire $questionnaire = null;

    public function __construct(int $value = null)
    {
        $this->value = $value ?? (int)date('Y');
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getGlobalObjective(): ?GlobalObjective
    {
        return $this->globalObjective;
    }

    public function setGlobalObjective(?GlobalObjective $globalObjective): self
    {
        $this->globalObjective = $globalObjective;
        if ($globalObjective && $globalObjective->getYear() !== $this) {
            $globalObjective->setYear($this);
        }
        return $this;
    }

    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(?Questionnaire $questionnaire): self
    {
        $this->questionnaire = $questionnaire;
        if ($questionnaire && $questionnaire->getYear() !== $this) {
            $questionnaire->setYear($this);
        }
        return $this;
    }

    public function hasGlobalObjective(): bool
    {
        return $this->globalObjective !== null;
    }

    public function hasQuestionnaire(): bool
    {
        return $this->questionnaire !== null;
    }
}

