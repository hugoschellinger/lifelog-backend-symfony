<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $receipter = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sendedAt = null;

    #[ORM\Column]
    private ?bool $isSended = null;

    public function __construct()
    {
        $this->sendedAt= new \DateTimeImmutable();
        $this->isSended=true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceipter(): ?string
    {
        return $this->receipter;
    }

    public function setReceipter(string $receipter): self
    {
        $this->receipter = $receipter;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSendedAt(): ?\DateTimeImmutable
    {
        return $this->sendedAt;
    }

    public function setSendedAt(\DateTimeImmutable $sendedAt): self
    {
        $this->sendedAt = $sendedAt;

        return $this;
    }

    public function isIsSended(): ?bool
    {
        return $this->isSended;
    }

    public function setIsSended(bool $isSended): self
    {
        $this->isSended = $isSended;

        return $this;
    }
}
