<?php

namespace App\Repository;

use App\Entity\Battle;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battle>
 */
class BattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battle::class);
    }

    public function findPendingBattlesForUser(User $user): array
    {
        $allPending = $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Filtrer en PHP pour éviter les problèmes de comparaison UUID
        return array_filter($allPending, function($battle) use ($user) {
            return $battle->getOpponent() && 
                   $battle->getOpponent()->getId()->toRfc4122() === $user->getId()->toRfc4122();
        });
    }

    public function findUserBattles(User $user): array
    {
        $allBattles = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        
        // Filtrer en PHP
        return array_filter($allBattles, function($battle) use ($user) {
            $userId = $user->getId()->toRfc4122();
            return ($battle->getChallenger() && $battle->getChallenger()->getId()->toRfc4122() === $userId) ||
                   ($battle->getOpponent() && $battle->getOpponent()->getId()->toRfc4122() === $userId);
        });
    }

    public function countUserWins(User $user): int
    {
        $completedBattles = $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getResult();
        
        // Compter les victoires en PHP
        $wins = 0;
        $userId = $user->getId()->toRfc4122();
        foreach ($completedBattles as $battle) {
            if ($battle->getWinner() && $battle->getWinner()->getId()->toRfc4122() === $userId) {
                $wins++;
            }
        }
        
        return $wins;
    }
}
