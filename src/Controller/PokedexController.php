<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PokedexController extends AbstractController
{
    private string $pokemonApiUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        string $pokemonApiUrl
    ) {
        $this->pokemonApiUrl = $pokemonApiUrl;
    }

    #[Route('/pokedex', name: 'app_pokedex')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        
        if ($request->query->has('loadMore')) {
            $currentCount = $session->get('pokedex_count', 50);
            $newCount = $currentCount + 50;
            $session->set('pokedex_count', $newCount);
        } else {
            $newCount = $session->get('pokedex_count', 50);
        }
        
        $pokemons = $this->cache->get("pokemons_limit_{$newCount}", function (ItemInterface $item) use ($newCount) {
            $item->expiresAfter(3600);
            
            $response = $this->httpClient->request('GET', "{$this->pokemonApiUrl}/pokemon?limit={$newCount}");
            $data = $response->toArray();
            
            $pokemons = [];
            foreach ($data['results'] as $pokemon) {
                preg_match('/\/(\d+)\/$/', $pokemon['url'], $matches);
                $id = $matches[1] ?? null;
                
                $pokemons[] = [
                    'id' => $id,
                    'name' => ucfirst($pokemon['name']),
                    'image' => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png"
                ];
            }
            
            return $pokemons;
        });

        return $this->render('pokedex/index.html.twig', [
            'pokemons' => $pokemons,
            'hasMore' => $newCount < 1000,
        ]);
    }

    #[Route('/pokedex/{id}', name: 'app_pokedex_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $pokemon = $this->cache->get("pokemon_detail_{$id}", function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600); //1h
            
            $response = $this->httpClient->request('GET', "{$this->pokemonApiUrl}/pokemon/{$id}");
            return $response->toArray();
        });

        return $this->render('pokedex/detail.html.twig', [
            'pokemon' => $pokemon,
        ]);
    }
}
