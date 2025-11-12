<?php

namespace App\Repository;

use App\Entity\TeamPokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamPokemon>
 */
class TeamPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamPokemon::class);
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.addedAt', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function countByUser($user): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasPokemon($user, int $pokemonId): bool
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.user = :user')
            ->andWhere('t.pokemonId = :pokemonId')
            ->setParameter('user', $user)
            ->setParameter('pokemonId', $pokemonId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
