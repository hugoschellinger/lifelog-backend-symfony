<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'questionnaire')]
class Questionnaire
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['questionnaire:read', 'questionnaire:write'])]
        private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['questionnaire:read', 'questionnaire:write'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['questionnaire:read', 'questionnaire:write'])]
    private string $questionnaireDescription;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['questionnaire:read', 'questionnaire:write'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['questionnaire:read', 'questionnaire:write'])]
    private bool $isActive = true;

    #[ORM\OneToOne(targetEntity: Year::class, inversedBy: 'questionnaire', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'year_id', referencedColumnName: 'id')]
    #[Ignore]
    private ?Year $year = null;

    // Relation supprimée car Question n'a pas de propriété 'questionnaire'
    // Les questions sont liées à Year, pas directement à Questionnaire
    // Utiliser getYear()->getQuestions() pour accéder aux questions
    // private Collection $questions;

    #[ORM\OneToMany(targetEntity: ResponseSession::class, mappedBy: 'questionnaire', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['questionnaire:read'])]
    private Collection $responseSessions;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        // $this->questions = new ArrayCollection(); // Supprimé car la relation n'existe plus
        $this->responseSessions = new ArrayCollection();
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

    public function getQuestionnaireDescription(): string
    {
        return $this->questionnaireDescription;
    }

    public function setQuestionnaireDescription(string $questionnaireDescription): self
    {
        $this->questionnaireDescription = $questionnaireDescription;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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
     * @return Collection<int, Question>
     * Méthode virtuelle qui récupère les questions via Year
     */
    public function getQuestions(): Collection
    {
        if ($this->year) {
            return $this->year->getQuestions();
        }
        return new ArrayCollection();
    }

    public function addQuestion(Question $question): self
    {
        // Les questions sont ajoutées via Year, pas directement via Questionnaire
        if ($this->year) {
            $this->year->addQuestion($question);
        }
        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        // Les questions sont supprimées via Year, pas directement via Questionnaire
        if ($this->year) {
            $this->year->removeQuestion($question);
        }
        return $this;
    }

    /**
     * @return Collection<int, ResponseSession>
     */
    public function getResponseSessions(): Collection
    {
        return $this->responseSessions;
    }

    public function addResponseSession(ResponseSession $responseSession): self
    {
        if (!$this->responseSessions->contains($responseSession)) {
            $this->responseSessions->add($responseSession);
            $responseSession->setQuestionnaire($this);
        }
        return $this;
    }

    public function removeResponseSession(ResponseSession $responseSession): self
    {
        if ($this->responseSessions->removeElement($responseSession)) {
            if ($responseSession->getQuestionnaire() === $this) {
                $responseSession->setQuestionnaire(null);
            }
        }
        return $this;
    }

    public function getCompletionPercentage(): float
    {
        $questions = $this->getQuestions();
        if ($questions->isEmpty()) {
            return 0;
        }

        $answeredQuestions = $questions->filter(fn(Question $question) => !$question->getAnswers()->isEmpty());
        return (count($answeredQuestions) / count($questions)) * 100;
    }

    public function getAnsweredQuestionsCount(): int
    {
        $questions = $this->getQuestions();
        return $questions->filter(fn(Question $question) => !$question->getAnswers()->isEmpty())->count();
    }

    public function getTotalQuestionsCount(): int
    {
        return $this->getQuestions()->count();
    }

    public function isCompleted(): bool
    {
        return $this->getCompletionPercentage() == 100;
    }

    public function getTotalResponseSessions(): int
    {
        return $this->responseSessions->count();
    }

    public function getCompletedSessions(): array
    {
        return $this->responseSessions->filter(fn(ResponseSession $session) => $session->isCompleted())->toArray();
    }

    public function getLatestSession(): ?ResponseSession
    {
        $sessions = $this->responseSessions->toArray();
        if (empty($sessions)) {
            return null;
        }

        usort($sessions, fn(ResponseSession $a, ResponseSession $b) => $b->getSessionDate() <=> $a->getSessionDate());
        return $sessions[0];
    }

    public function canCreateNewSession(): bool
    {
        return !$this->getQuestions()->isEmpty();
    }
}

