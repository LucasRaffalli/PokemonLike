<?php

namespace App\Controller;

use App\Entity\Battle;
use App\Repository\BattleRepository;
use App\Repository\UserRepository;
use App\Service\BattleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class BattleController extends AbstractController
{
    #[Route('/battle', name: 'app_battle')]
    #[IsGranted('ROLE_MEMBER')]
    public function index(BattleRepository $battleRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer tous les utilisateurs sauf l'utilisateur connecté
        $availableOpponents = $userRepository->createQueryBuilder('u')
            ->where('u.id != :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();

        // Récupérer les défis en attente
        $pendingBattles = $battleRepository->findPendingBattlesForUser($user);
        
        // Récupérer l'historique des combats
        $battleHistory = $battleRepository->findUserBattles($user);
        
        // Statistiques
        $wins = $battleRepository->countUserWins($user);
        $totalBattles = count(array_filter($battleHistory, fn($b) => $b->getStatus() === 'completed'));
        $losses = $totalBattles - $wins;

        return $this->render('battle/index.html.twig', [
            'availableOpponents' => $availableOpponents,
            'pendingBattles' => $pendingBattles,
            'battleHistory' => $battleHistory,
            'wins' => $wins,
            'losses' => $losses,
            'totalBattles' => $totalBattles,
        ]);
    }

    #[Route('/battle/challenge/{id}', name: 'app_battle_challenge', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBER')]
    public function challenge(string $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifier le token CSRF
        $token = new CsrfToken('battle_challenge_' . $id, $request->request->get('_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_battle');
        }

        $challenger = $this->getUser();
        $opponent = $userRepository->find($id);

        if (!$opponent) {
            $this->addFlash('error', 'Adversaire introuvable.');
            return $this->redirectToRoute('app_battle');
        }

        if ($opponent === $challenger) {
            $this->addFlash('error', 'Vous ne pouvez pas vous défier vous-même !');
            return $this->redirectToRoute('app_battle');
        }

        // Vérifier que le challenger a une équipe
        if ($challenger->getTeamPokemons()->isEmpty()) {
            $this->addFlash('error', 'Vous devez avoir au moins un Pokémon dans votre équipe pour lancer un défi !');
            return $this->redirectToRoute('app_battle');
        }

        $battle = new Battle();
        $battle->setChallenger($challenger);
        $battle->setOpponent($opponent);

        $entityManager->persist($battle);
        $entityManager->flush();

        $this->addFlash('success', "Défi envoyé à {$opponent->getPseudo()} !");

        return $this->redirectToRoute('app_battle');
    }

    #[Route('/battle/accept/{id}', name: 'app_battle_accept', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBER')]
    public function accept(Battle $battle, Request $request, BattleService $battleService, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifier le token CSRF
        $token = new CsrfToken('battle_accept_' . $battle->getId(), $request->request->get('_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_battle');
        }

        $user = $this->getUser();

        if ($battle->getOpponent() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($battle->getStatus() !== 'pending') {
            $this->addFlash('error', 'Ce combat n\'est plus disponible.');
            return $this->redirectToRoute('app_battle');
        }

        // Vérifier que l'adversaire a une équipe
        if ($user->getTeamPokemons()->isEmpty()) {
            $this->addFlash('error', 'Vous devez avoir au moins un Pokémon dans votre équipe pour accepter un défi !');
            return $this->redirectToRoute('app_battle');
        }

        $battle->setStatus('in_progress');
        
        // Simuler le combat
        $battleService->simulateBattle($battle);

        $entityManager->flush();

        $this->addFlash('success', 'Combat terminé ! Consultez les résultats ci-dessous.');

        return $this->redirectToRoute('app_battle_view', ['id' => $battle->getId()]);
    }

    #[Route('/battle/decline/{id}', name: 'app_battle_decline', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBER')]
    public function decline(Battle $battle, Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifier le token CSRF
        $token = new CsrfToken('battle_decline_' . $battle->getId(), $request->request->get('_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_battle');
        }

        $user = $this->getUser();

        if ($battle->getOpponent() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($battle->getStatus() !== 'pending') {
            $this->addFlash('error', 'Ce combat n\'est plus disponible.');
            return $this->redirectToRoute('app_battle');
        }

        $battle->setStatus('cancelled');
        $entityManager->flush();

        $this->addFlash('success', 'Défi refusé.');

        return $this->redirectToRoute('app_battle');
    }

    #[Route('/battle/view/{id}', name: 'app_battle_view')]
    #[IsGranted('ROLE_MEMBER')]
    public function view(Battle $battle): Response
    {
        $user = $this->getUser();

        // Vérifier que l'utilisateur est impliqué dans ce combat
        if ($battle->getChallenger() !== $user && $battle->getOpponent() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('battle/view.html.twig', [
            'battle' => $battle,
        ]);
    }
}
