<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

#[ORM\Entity(repositoryClass: VilleRepository::class)]

#[UniqueEntity(
    fields: 'nom',
    message: 'Ce nom de ville est déjà utilisé.'
)]

#[UniqueEntity(
    fields: 'codePostal',
    message: 'Le code postal est déjà utilisé.'
)]

class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Regex(
        pattern: '/^\D*$/',
        message: 'Le nom de la ville ne peut pas contenir de chiffres.'
    )]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[Regex(
        pattern: '/^\d+$/',
        message: 'Le code postal ne peut contenir que des chiffres.'
    )]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 5,
        max: 5,
        minMessage: "Il faut {{ limit }} caractères minimum !",
        maxMessage: "Vous ne pouvez pas dépasser {{ limit }} caractères !"
    )]
    #[ORM\Column(length: 255)]
    private ?string $codePostal = null;

    #[ORM\OneToMany(mappedBy: 'ville', targetEntity: Lieu::class, cascade:['remove'])]
    private Collection $lieu;

    public function __construct()
    {
        $this->lieu = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieu(): Collection
    {
        return $this->lieu;
    }

    public function addLieu(Lieu $lieu): self
    {
        if (!$this->lieu->contains($lieu)) {
            $this->lieu->add($lieu);
            $lieu->setVille($this);
        }

        return $this;
    }

    public function removeLieu(Lieu $lieu): self
    {
        if ($this->lieu->removeElement($lieu)) {
            // set the owning side to null (unless already changed)
            if ($lieu->getVille() === $this) {
                $lieu->setVille(null);
            }
        }

        return $this;
    }
}
