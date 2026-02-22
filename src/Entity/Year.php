<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'year')]
#[ORM\UniqueConstraint(name: 'unique_value', columns: ['value'])]
class Year
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['year:read', 'year:write', 'question:read'])]
        private ?int $id = null;

    #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['year:read', 'year:write', 'question:read'])]
    private int $value;

    #[ORM\OneToOne(targetEntity: GlobalObjective::class, mappedBy: 'year', cascade: ['persist', 'remove'])]
    #[Ignore]
    private ?GlobalObjective $globalObjective = null;

    #[ORM\OneToOne(targetEntity: Questionnaire::class, mappedBy: 'year', cascade: ['persist', 'remove'])]
    #[Ignore]
    private ?Questionnaire $questionnaire = null;

    #[ORM\OneToOne(targetEntity: Retrospection::class, mappedBy: 'year', cascade: ['persist', 'remove'])]
    #[Ignore]
    private ?Retrospection $retrospection = null;

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'year', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    #[Groups(['year:read'])] // pas dans question:read pour éviter Question → year → questions → Question
    private Collection $questions;

    public function __construct(int $value = null)
    {
        $this->value = $value ?? (int)date('Y');
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getRetrospection(): ?Retrospection
    {
        return $this->retrospection;
    }

    public function setRetrospection(?Retrospection $retrospection): self
    {
        $this->retrospection = $retrospection;
        if ($retrospection && $retrospection->getYear() !== $this) {
            $retrospection->setYear($this);
        }
        return $this;
    }

    public function hasRetrospection(): bool
    {
        return $this->retrospection !== null;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setYear($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getYear() === $this) {
                $question->setYear(null);
            }
        }
        return $this;
    }
}

