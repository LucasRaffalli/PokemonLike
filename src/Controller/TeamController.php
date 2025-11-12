<?php

namespace App\Controller;

use App\Entity\TeamPokemon;
use App\Repository\TeamPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    #[IsGranted('ROLE_MEMBER')]
    public function index(TeamPokemonRepository $teamPokemonRepository): Response
    {
        $user = $this->getUser();
        
        $teamPokemons = $user->getTeamPokemons()->toArray();
        $teamCount = count($teamPokemons);

        return $this->render('team/index.html.twig', [
            'user' => $user,
            'teamPokemons' => $teamPokemons,
            'teamCount' => $teamCount,
        ]);
    }

    #[Route('/team/add/{pokemonId}', name: 'app_team_add', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBER')]
    public function add(
        int $pokemonId,
        Request $request,
        TeamPokemonRepository $teamPokemonRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        if ($teamPokemonRepository->countByUser($user) >= 6) {
            $this->addFlash('error', 'Votre équipe est complète ! Maximum 6 Pokémon.');
            return $this->redirectToRoute('app_team');
        }

        if ($teamPokemonRepository->hasPokemon($user, $pokemonId)) {
            $this->addFlash('error', 'Ce Pokémon est déjà dans votre équipe !');
            return $this->redirectToRoute('app_team');
        }

        $pokemonName = $request->request->get('pokemonName');
        $pokemonImage = $request->request->get('pokemonImage');

        $teamPokemon = new TeamPokemon();
        $teamPokemon->setUser($user);
        $teamPokemon->setPokemonId($pokemonId);
        $teamPokemon->setPokemonName($pokemonName);
        $teamPokemon->setPokemonImage($pokemonImage);

        $entityManager->persist($teamPokemon);
        $entityManager->flush();

        $this->addFlash('success', "{$pokemonName} a été ajouté à votre équipe !");

        return $this->redirectToRoute('app_team');
    }

    #[Route('/team/remove/{id}', name: 'app_team_remove', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBER')]
    public function remove(TeamPokemon $teamPokemon, EntityManagerInterface $entityManager): Response
    {
        if ($teamPokemon->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $pokemonName = $teamPokemon->getPokemonName();
        $entityManager->remove($teamPokemon);
        $entityManager->flush();

        $this->addFlash('success', "{$pokemonName} a été retiré de votre équipe.");

        return $this->redirectToRoute('app_team');
    }
}
