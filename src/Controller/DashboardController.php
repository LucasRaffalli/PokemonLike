<?php

namespace App\Controller;

use App\Repository\BattleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_MEMBER')]
    public function index(BattleRepository $battleRepository): Response
    {
        $user = $this->getUser();
        $teamCount = $user->getTeamPokemons()->count();
        
        // Statistiques de combat
        $wins = $battleRepository->countUserWins($user);
        $pendingBattles = $battleRepository->findPendingBattlesForUser($user);

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'teamCount' => $teamCount,
            'wins' => $wins,
            'pendingBattlesCount' => count($pendingBattles),
        ]);
    }
}
