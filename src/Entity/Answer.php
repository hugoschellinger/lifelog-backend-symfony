<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'answer')]
class Answer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $textValue = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?float $numberValue = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?\DateTimeInterface $dateValue = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?bool $boolValue = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['answer:read', 'answer:write'])]
    private array $selectedOptions = [];

    #[ORM\Column(type: 'datetime')]
    #[Groups(['answer:read', 'answer:write'])]
    private \DateTimeInterface $answeredAt;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[Groups(['answer:read'])]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: ResponseSession::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'response_session_id', referencedColumnName: 'id')]
    #[Groups(['answer:read'])]
    private ?ResponseSession $responseSession = null;

    public function __construct()
    {
        $this->answeredAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): self
    {
        $this->textValue = $textValue;
        return $this;
    }

    public function getNumberValue(): ?float
    {
        return $this->numberValue;
    }

    public function setNumberValue(?float $numberValue): self
    {
        $this->numberValue = $numberValue;
        return $this;
    }

    public function getDateValue(): ?\DateTimeInterface
    {
        return $this->dateValue;
    }

    public function setDateValue(?\DateTimeInterface $dateValue): self
    {
        $this->dateValue = $dateValue;
        return $this;
    }

    public function getBoolValue(): ?bool
    {
        return $this->boolValue;
    }

    public function setBoolValue(?bool $boolValue): self
    {
        $this->boolValue = $boolValue;
        return $this;
    }

    public function getSelectedOptions(): array
    {
        return $this->selectedOptions;
    }

    public function setSelectedOptions(array $selectedOptions): self
    {
        $this->selectedOptions = $selectedOptions;
        return $this;
    }

    public function getAnsweredAt(): \DateTimeInterface
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(\DateTimeInterface $answeredAt): self
    {
        $this->answeredAt = $answeredAt;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getResponseSession(): ?ResponseSession
    {
        return $this->responseSession;
    }

    public function setResponseSession(?ResponseSession $responseSession): self
    {
        $this->responseSession = $responseSession;
        return $this;
    }

    public function getDisplayValue(): string
    {
        if ($this->textValue !== null) {
            return $this->textValue;
        }
        if ($this->numberValue !== null) {
            return (string)$this->numberValue;
        }
        if ($this->dateValue !== null) {
            return $this->dateValue->format('d/m/Y');
        }
        if ($this->boolValue !== null) {
            return $this->boolValue ? 'Oui' : 'Non';
        }
        if (!empty($this->selectedOptions)) {
            return implode(', ', $this->selectedOptions);
        }
        return 'Aucune réponse';
    }
}

