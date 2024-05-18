<?php

namespace App\Entity;

use App\Repository\ExceptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ExceptionRepository::class)]
class Exception
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $code = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $throwableAt = null;

    public function __construct()
    {
        $this->throwableAt=new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = substr($message,0,255);

        return $this;
    }

    public function getThrowableAt(): ?\DateTimeImmutable
    {
        return $this->throwableAt;
    }

    public function setThrowableAt(\DateTimeImmutable $throwableAt): self
    {
        $this->throwableAt = $throwableAt;

        return $this;
    }
}
