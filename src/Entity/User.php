<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, TeamPokemon>
     */
    #[ORM\OneToMany(targetEntity: TeamPokemon::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $teamPokemons;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->roles = ['ROLE_MEMBER'];
        $this->teamPokemons = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_MEMBER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return Collection<int, TeamPokemon>
     */
    public function getTeamPokemons(): Collection
    {
        return $this->teamPokemons;
    }

    public function addTeamPokemon(TeamPokemon $teamPokemon): static
    {
        if (!$this->teamPokemons->contains($teamPokemon)) {
            $this->teamPokemons->add($teamPokemon);
            $teamPokemon->setUser($this);
        }

        return $this;
    }

    public function removeTeamPokemon(TeamPokemon $teamPokemon): static
    {
        if ($this->teamPokemons->removeElement($teamPokemon)) {
            if ($teamPokemon->getUser() === $this) {
                $teamPokemon->setUser(null);
            }
        }

        return $this;
    }
}
