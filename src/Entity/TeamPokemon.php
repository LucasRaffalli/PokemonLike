<?php

namespace App\Entity;

use App\Repository\TeamPokemonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamPokemonRepository::class)]
class TeamPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'teamPokemons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est requis')]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'ID du Pokémon est requis')]
    #[Assert\Positive(message: 'L\'ID du Pokémon doit être positif')]
    #[Assert\LessThanOrEqual(1025, message: 'L\'ID du Pokémon ne peut pas dépasser 1025')]
    private ?int $pokemonId = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du Pokémon est requis')]
    #[Assert\Length(
        min: 1,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractère',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $pokemonName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL de l\'image n\'est pas valide')]
    private ?string $pokemonImage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPokemonId(): ?int
    {
        return $this->pokemonId;
    }

    public function setPokemonId(int $pokemonId): static
    {
        $this->pokemonId = $pokemonId;
        return $this;
    }

    public function getPokemonName(): ?string
    {
        return $this->pokemonName;
    }

    public function setPokemonName(string $pokemonName): static
    {
        $this->pokemonName = $pokemonName;
        return $this;
    }

    public function getPokemonImage(): ?string
    {
        return $this->pokemonImage;
    }

    public function setPokemonImage(?string $pokemonImage): static
    {
        $this->pokemonImage = $pokemonImage;
        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;
        return $this;
    }
}
