<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\VirtualProperty;

#[ORM\Entity]
#[ORM\Table(name: 'response_session')]
class ResponseSession
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['response_session:read', 'response_session:write', 'answer:read'])]
        private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['response_session:read', 'response_session:write', 'answer:read'])]
    private \DateTimeInterface $sessionDate;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['response_session:read', 'response_session:write', 'answer:read'])]
    private bool $isCompleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['response_session:read', 'response_session:write', 'answer:read'])]
    private ?\DateTimeInterface $completionDate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['response_session:read', 'response_session:write', 'answer:read'])]
    private ?string $sessionTitle = null;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class, inversedBy: 'responseSessions')]
    #[ORM\JoinColumn(name: 'questionnaire_id', referencedColumnName: 'id')]
    #[Groups(['response_session:read'])]
    private ?Questionnaire $questionnaire = null;

    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'responseSession', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['response_session:read'])]
    private Collection $answers;

    public function __construct()
    {
        $this->sessionDate = new \DateTime();
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionDate(): \DateTimeInterface
    {
        return $this->sessionDate;
    }

    public function setSessionDate(\DateTimeInterface $sessionDate): self
    {
        $this->sessionDate = $sessionDate;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): self
    {
        $this->isCompleted = $isCompleted;
        if ($isCompleted && $this->completionDate === null) {
            $this->completionDate = new \DateTime();
        }
        return $this;
    }

    public function getCompletionDate(): ?\DateTimeInterface
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTimeInterface $completionDate): self
    {
        $this->completionDate = $completionDate;
        return $this;
    }

    public function getSessionTitle(): ?string
    {
        return $this->sessionTitle;
    }

    public function setSessionTitle(?string $sessionTitle): self
    {
        $this->sessionTitle = $sessionTitle;
        return $this;
    }

    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(?Questionnaire $questionnaire): self
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setResponseSession($this);
        }
        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getResponseSession() === $this) {
                $answer->setResponseSession(null);
            }
        }
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->isCompleted = true;
        $this->completionDate = new \DateTime();
        return $this;
    }

    public function getCompletionPercentage(): float
    {
        if (!$this->questionnaire || $this->questionnaire->getQuestions()->isEmpty()) {
            return 0;
        }

        $answeredQuestionIds = [];
        foreach ($this->answers as $answer) {
            if ($answer->getQuestion()) {
                $answeredQuestionIds[$answer->getQuestion()->getId()] = true;
            }
        }

        return (count($answeredQuestionIds) / $this->questionnaire->getQuestions()->count()) * 100;
    }

    public function getAnsweredQuestionsCount(): int
    {
        $answeredQuestionIds = [];
        foreach ($this->answers as $answer) {
            if ($answer->getQuestion()) {
                $answeredQuestionIds[$answer->getQuestion()->getId()] = true;
            }
        }
        return count($answeredQuestionIds);
    }

    public function getSessionDuration(): ?\DateInterval
    {
        if (!$this->completionDate) {
            return null;
        }

        return $this->sessionDate->diff($this->completionDate);
    }
    
    #[VirtualProperty]
    #[Groups(['response_session:read'])]
    public function getAnswersCount(): int
    {
        return $this->answers->count();
    }
}

