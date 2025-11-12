<?php

namespace App\Command;

use App\Entity\TeamPokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update-pokemon-names',
    description: 'Met à jour les noms des Pokémon depuis l\'API',
)]
class UpdatePokemonNamesCommand extends Command
{
    private string $pokemonApiUrl;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        string $pokemonApiUrl
    ) {
        $this->pokemonApiUrl = $pokemonApiUrl;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des noms de Pokémon');

        $teamPokemons = $this->entityManager->getRepository(TeamPokemon::class)->findAll();

        $progressBar = $io->createProgressBar(count($teamPokemons));
        $progressBar->start();

        foreach ($teamPokemons as $teamPokemon) {
            try {
                $response = $this->httpClient->request('GET', "{$this->pokemonApiUrl}/pokemon/{$teamPokemon->getPokemonId()}");
                $data = $response->toArray();
                
                $name = ucfirst($data['name']);
                $teamPokemon->setPokemonName($name);
                
                $this->entityManager->flush();
                
                $progressBar->advance();
                
                // Pause pour ne pas surcharger l'API
                usleep(100000); // 0.1 seconde
            } catch (\Exception $e) {
                $io->warning("Erreur pour le Pokémon #{$teamPokemon->getPokemonId()}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $io->newLine(2);

        $io->success('Tous les noms de Pokémon ont été mis à jour !');

        return Command::SUCCESS;
    }
}
