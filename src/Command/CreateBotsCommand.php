<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\TeamPokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-bots',
    description: 'Crée des adversaires bots avec des équipes aléatoires',
)]
class CreateBotsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $bots = [
            ['pseudo' => 'Sacha_BOT', 'email' => 'sacha@bot.com'],
            ['pseudo' => 'Ondine_BOT', 'email' => 'ondine@bot.com'],
            ['pseudo' => 'Pierre_BOT', 'email' => 'pierre@bot.com'],
            ['pseudo' => 'Flora_BOT', 'email' => 'flora@bot.com'],
            ['pseudo' => 'Red_BOT', 'email' => 'red@bot.com'],
        ];

        $io->title('Création des bots adversaires');

        foreach ($bots as $botData) {
            // Vérifier si le bot existe déjà
            $existingBot = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $botData['email']]);
            
            if ($existingBot) {
                $io->warning("Le bot {$botData['pseudo']} existe déjà");
                continue;
            }

            // Créer le bot
            $bot = new User();
            $bot->setPseudo($botData['pseudo']);
            $bot->setEmail($botData['email']);
            $bot->setPassword($this->passwordHasher->hashPassword($bot, 'bot123'));
            
            $this->entityManager->persist($bot);
            $this->entityManager->flush();

            // Créer une équipe aléatoire (3 à 6 Pokémon)
            $teamSize = rand(3, 6);
            $usedPokemonIds = [];

            for ($i = 0; $i < $teamSize; $i++) {
                // Générer un ID aléatoire entre 1 et 151 (Pokémon de la 1ère génération)
                do {
                    $pokemonId = rand(1, 151);
                } while (in_array($pokemonId, $usedPokemonIds));
                
                $usedPokemonIds[] = $pokemonId;

                // Nom générique (on pourrait améliorer avec l'API)
                $pokemonName = "Pokemon #{$pokemonId}";
                $pokemonImage = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$pokemonId}.png";

                $teamPokemon = new TeamPokemon();
                $teamPokemon->setUser($bot);
                $teamPokemon->setPokemonId($pokemonId);
                $teamPokemon->setPokemonName($pokemonName);
                $teamPokemon->setPokemonImage($pokemonImage);

                $this->entityManager->persist($teamPokemon);
            }

            $this->entityManager->flush();

            $io->success("Bot {$botData['pseudo']} créé avec {$teamSize} Pokémon");
        }

        $io->success('Tous les bots ont été créés avec succès !');

        return Command::SUCCESS;
    }
}
