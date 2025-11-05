<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'question')]
class Question
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['question:read', 'question:write'])]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['question:read', 'question:write'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['question:read', 'question:write'])]
    private ?string $questionDescription = null;

    #[ORM\Column(type: 'string', enumType: QuestionType::class)]
    #[Groups(['question:read', 'question:write'])]
    private QuestionType $type;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['question:read', 'question:write'])]
    private bool $isRequired = false;

    #[ORM\Column(type: 'integer')]
    #[Groups(['question:read', 'question:write'])]
    private int $order = 0;

    #[ORM\Column(type: 'json')]
    #[Groups(['question:read', 'question:write'])]
    private array $options = [];

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['question:read', 'question:write'])]
    private ?float $minValue = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['question:read', 'question:write'])]
    private ?float $maxValue = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['question:read', 'question:write'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'questionnaire_id', referencedColumnName: 'id')]
    #[Groups(['question:read'])]
    private ?Questionnaire $questionnaire = null;

    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['question:read'])]
    private Collection $answers;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->answers = new ArrayCollection();
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

    public function getQuestionDescription(): ?string
    {
        return $this->questionDescription;
    }

    public function setQuestionDescription(?string $questionDescription): self
    {
        $this->questionDescription = $questionDescription;
        return $this;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }

    public function setType(QuestionType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getMinValue(): ?float
    {
        return $this->minValue;
    }

    public function setMinValue(?float $minValue): self
    {
        $this->minValue = $minValue;
        return $this;
    }

    public function getMaxValue(): ?float
    {
        return $this->maxValue;
    }

    public function setMaxValue(?float $maxValue): self
    {
        $this->maxValue = $maxValue;
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
            $answer->setQuestion($this);
        }
        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }
        return $this;
    }

    public function getLatestAnswer(): ?Answer
    {
        $answers = $this->answers->toArray();
        if (empty($answers)) {
            return null;
        }

        usort($answers, fn(Answer $a, Answer $b) => $b->getAnsweredAt() <=> $a->getAnsweredAt());
        return $answers[0];
    }

    public function isAnswered(): bool
    {
        return !$this->answers->isEmpty();
    }
}

