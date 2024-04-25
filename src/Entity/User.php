<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["User:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(["User:read"])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["User:read"])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable:true)]
    #[Groups(["User:read"])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["User:read"])]
    private ?string $lastname = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    private ?\DateTimeImmutable $lastConnexion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Device::class, orphanRemoval: true, cascade:["persist"])]
    private Collection $device;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $googleIdToken = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $appleIdentifyToken = null;

    public function __construct()
    {
        $this->lastConnexion=new DateTimeImmutable();
        $this->device = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastConnexion(): ?\DateTimeImmutable
    {
        return $this->lastConnexion;
    }

    public function setLastConnexion(\DateTimeImmutable $lastConnexion): self
    {
        $this->lastConnexion = $lastConnexion;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): self
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function getPasswordToken(): ?string
    {
        return $this->passwordToken;
    }

    public function setPasswordToken(?string $passwordToken): self
    {
        $this->passwordToken = $passwordToken;

        return $this;
    }

    public function getResetAt(): ?\DateTimeImmutable
    {
        return $this->resetAt;
    }

    public function setResetAt(?\DateTimeImmutable $resetAt): self
    {
        $this->resetAt = $resetAt;

        return $this;
    }

    /**
     * @return Collection<int, device>
     */
    public function getDevice(): Collection
    {
        return $this->device;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->device->contains($device)) {
            $this->device->add($device);
            $device->setUser($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->device->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getUser() === $this) {
                $device->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getGoogleIdToken(): ?string
    {
        return $this->googleIdToken;
    }

    public function setGoogleIdToken(?string $googleIdToken): self
    {
        $this->googleIdToken = $googleIdToken;

        return $this;
    }

    public function getAppleIdentifyToken(): ?string
    {
        return $this->appleIdentifyToken;
    }

    public function setAppleIdentifyToken(?string $appleIdentifyToken): static
    {
        $this->appleIdentifyToken = $appleIdentifyToken;

        return $this;
    }
}
