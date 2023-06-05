<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le pseudo ne peut pas être null")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[Assert\Length(
        min: 6,
        max: 255,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(message: "Le nom ne peut pas être null")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Le prénom ne peut pas être null")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 100)]
    private ?string $prenom = null;
    #[ORM\Column(length: 10)]
    private ?string $telephone = null;

    #[Assert\NotBlank(message: "Le mail ne peut pas être null")]
    #[Assert\Email()]
    #[Assert\Length(
        min: 4,
        max: 255,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $mail = null;

    #[ORM\Column]
    private ?bool $actif = true;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: Sortie::class)]
    private Collection $sortie;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $sortieParticipant;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    public function __construct()
    {
        $this->sortie = new ArrayCollection();
        $this->sortieParticipant = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortie(): Collection
    {
        return $this->sortie;
    }

    public function addSortie(Sortie $sortie): self
    {
        if (!$this->sortie->contains($sortie)) {
            $this->sortie->add($sortie);
            $sortie->setParticipant($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): self
    {
        if ($this->sortie->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getParticipant() === $this) {
                $sortie->setParticipant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortieParticipant(): Collection
    {
        return $this->sortieParticipant;
    }

    public function addSortieParticipant(Sortie $sortieParticipant): self
    {
        if (!$this->sortieParticipant->contains($sortieParticipant)) {
            $this->sortieParticipant->add($sortieParticipant);
        }

        return $this;
    }

    public function removeSortieParticipant(Sortie $sortieParticipant): self
    {
        $this->sortieParticipant->removeElement($sortieParticipant);

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }
}
