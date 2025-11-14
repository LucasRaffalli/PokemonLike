<?php

namespace App\Service;

use App\Entity\Battle;
use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BattleService
{
    private string $pokemonApiUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        string $pokemonApiUrl
    ) {
        $this->pokemonApiUrl = $pokemonApiUrl;
    }

    public function simulateBattle(Battle $battle): void
    {
        $challenger = $battle->getChallenger();
        $opponent = $battle->getOpponent();

        $battle->addLogEntry("⚔️ Combat Pokémon entre {$challenger->getPseudo()} et {$opponent->getPseudo()}");
        $battle->addLogEntry("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $challengerTeam = $challenger->getTeamPokemons()->toArray();
        $opponentTeam = $opponent->getTeamPokemons()->toArray();

        if (empty($challengerTeam)) {
            $battle->addLogEntry("❌ {$challenger->getPseudo()} n'a pas d'équipe !");
            $battle->setWinner($opponent);
            return;
        }

        if (empty($opponentTeam)) {
            $battle->addLogEntry("❌ {$opponent->getPseudo()} n'a pas d'équipe !");
            $battle->setWinner($challenger);
            return;
        }

        $battle->addLogEntry("📊 {$challenger->getPseudo()} envoie " . count($challengerTeam) . " Pokémon au combat");
        $battle->addLogEntry("📊 {$opponent->getPseudo()} envoie " . count($opponentTeam) . " Pokémon au combat");
        $battle->addLogEntry("");

        $challengerAlive = count($challengerTeam);
        $opponentAlive = count($opponentTeam);
        $currentChallenger = 0;
        $currentOpponent = 0;

        $challengerPokemon = $challengerTeam[$currentChallenger];
        $opponentPokemon = $opponentTeam[$currentOpponent];
        
        $challengerStats = $this->getPokemonStats($challengerPokemon->getPokemonId());
        $opponentStats = $this->getPokemonStats($opponentPokemon->getPokemonId());
        
        $challengerHP = $challengerStats['hp'];
        $opponentHP = $opponentStats['hp'];
        
        $battle->addLogEntry("🔴 {$challenger->getPseudo()} envoie {$challengerPokemon->getPokemonName()} (HP: {$challengerHP})");
        $battle->addLogEntry("🔵 {$opponent->getPseudo()} envoie {$opponentPokemon->getPokemonName()} (HP: {$opponentHP})");
        $battle->addLogEntry("");

        $turn = 1;
        $maxTurns = 100;

        while ($challengerAlive > 0 && $opponentAlive > 0 && $turn <= $maxTurns) {
            $battle->addLogEntry("━━━ Tour {$turn} ━━━");
            
            // Déterminer qui attaque en premier (basé sur la vitesse)
            if ($challengerStats['speed'] >= $opponentStats['speed']) {
                // Challenger attaque en premier
                $damage = $this->calculateDamage($challengerStats, $opponentStats);
                $opponentHP -= $damage;
                $battle->addLogEntry("⚡ {$challengerPokemon->getPokemonName()} attaque {$opponentPokemon->getPokemonName()} ! -{$damage} HP");
                
                if ($opponentHP <= 0) {
                    $opponentHP = 0;
                    $battle->addLogEntry("💀 {$opponentPokemon->getPokemonName()} est K.O. !");
                    $opponentAlive--;
                    
                    if ($opponentAlive > 0) {
                        $currentOpponent++;
                        $opponentPokemon = $opponentTeam[$currentOpponent];
                        $opponentStats = $this->getPokemonStats($opponentPokemon->getPokemonId());
                        $opponentHP = $opponentStats['hp'];
                        $battle->addLogEntry("🔵 {$opponent->getPseudo()} envoie {$opponentPokemon->getPokemonName()} (HP: {$opponentHP})");
                    }
                } else {
                    $battle->addLogEntry("   {$opponentPokemon->getPokemonName()}: {$opponentHP} HP restants");
                    
                    // L'adversaire riposte
                    $damage = $this->calculateDamage($opponentStats, $challengerStats);
                    $challengerHP -= $damage;
                    $battle->addLogEntry("⚡ {$opponentPokemon->getPokemonName()} contre-attaque ! -{$damage} HP");
                    
                    if ($challengerHP <= 0) {
                        $challengerHP = 0;
                        $battle->addLogEntry("� {$challengerPokemon->getPokemonName()} est K.O. !");
                        $challengerAlive--;
                        
                        if ($challengerAlive > 0) {
                            $currentChallenger++;
                            $challengerPokemon = $challengerTeam[$currentChallenger];
                            $challengerStats = $this->getPokemonStats($challengerPokemon->getPokemonId());
                            $challengerHP = $challengerStats['hp'];
                            $battle->addLogEntry("🔴 {$challenger->getPseudo()} envoie {$challengerPokemon->getPokemonName()} (HP: {$challengerHP})");
                        }
                    } else {
                        $battle->addLogEntry("   {$challengerPokemon->getPokemonName()}: {$challengerHP} HP restants");
                    }
                }
            } else {
                // adversaires attaque en premier
                $damage = $this->calculateDamage($opponentStats, $challengerStats);
                $challengerHP -= $damage;
                $battle->addLogEntry("⚡ {$opponentPokemon->getPokemonName()} attaque {$challengerPokemon->getPokemonName()} ! -{$damage} HP");
                
                if ($challengerHP <= 0) {
                    $challengerHP = 0;
                    $battle->addLogEntry("💀 {$challengerPokemon->getPokemonName()} est K.O. !");
                    $challengerAlive--;
                    
                    if ($challengerAlive > 0) {
                        $currentChallenger++;
                        $challengerPokemon = $challengerTeam[$currentChallenger];
                        $challengerStats = $this->getPokemonStats($challengerPokemon->getPokemonId());
                        $challengerHP = $challengerStats['hp'];
                        $battle->addLogEntry("🔴 {$challenger->getPseudo()} envoie {$challengerPokemon->getPokemonName()} (HP: {$challengerHP})");
                    }
                } else {
                    $battle->addLogEntry("   {$challengerPokemon->getPokemonName()}: {$challengerHP} HP restants");
                    
                    // Le challenger riposte
                    $damage = $this->calculateDamage($challengerStats, $opponentStats);
                    $opponentHP -= $damage;
                    $battle->addLogEntry("⚡ {$challengerPokemon->getPokemonName()} contre-attaque ! -{$damage} HP");
                    
                    if ($opponentHP <= 0) {
                        $opponentHP = 0;
                        $battle->addLogEntry("💀 {$opponentPokemon->getPokemonName()} est K.O. !");
                        $opponentAlive--;
                        
                        if ($opponentAlive > 0) {
                            $currentOpponent++;
                            $opponentPokemon = $opponentTeam[$currentOpponent];
                            $opponentStats = $this->getPokemonStats($opponentPokemon->getPokemonId());
                            $opponentHP = $opponentStats['hp'];
                            $battle->addLogEntry("🔵 {$opponent->getPseudo()} envoie {$opponentPokemon->getPokemonName()} (HP: {$opponentHP})");
                        }
                    } else {
                        $battle->addLogEntry("   {$opponentPokemon->getPokemonName()}: {$opponentHP} HP restants");
                    }
                }
            }
            
            $battle->addLogEntry("");
            $turn++;
        }

        $battle->addLogEntry("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $battle->addLogEntry("");
        
        // Déterminer le gagnant
        if ($challengerAlive > 0 && $opponentAlive == 0) {
            $battle->setWinner($challenger);
            $battle->addLogEntry("🏆 {$challenger->getPseudo()} remporte le combat !");
            $battle->addLogEntry("   Pokémon restants: {$challengerAlive}");
        } elseif ($opponentAlive > 0 && $challengerAlive == 0) {
            $battle->setWinner($opponent);
            $battle->addLogEntry("🏆 {$opponent->getPseudo()} remporte le combat !");
            $battle->addLogEntry("   Pokémon restants: {$opponentAlive}");
        } else {
            $battle->addLogEntry("🤝 Match nul ! Les deux dresseurs sont à bout de forces.");
        }

        $battle->setStatus('completed');
        $battle->setCompletedAt(new \DateTimeImmutable());
    }

    private function getPokemonStats(int $pokemonId): array
    {
        return $this->cache->get("pokemon_stats_{$pokemonId}", function (ItemInterface $item) use ($pokemonId) {
            $item->expiresAfter(86400); // 24h
            
            try {
                $response = $this->httpClient->request('GET', "{$this->pokemonApiUrl}/pokemon/{$pokemonId}");
                $data = $response->toArray();
                
                $stats = [
                    'hp' => 0,
                    'attack' => 0,
                    'defense' => 0,
                    'speed' => 0,
                ];
                
                foreach ($data['stats'] as $stat) {
                    $statName = $stat['stat']['name'];
                    $baseStat = $stat['base_stat'];
                    
                    if ($statName === 'hp') {
                        $stats['hp'] = $baseStat;
                    } elseif ($statName === 'attack') {
                        $stats['attack'] = $baseStat;
                    } elseif ($statName === 'defense') {
                        $stats['defense'] = $baseStat;
                    } elseif ($statName === 'speed') {
                        $stats['speed'] = $baseStat;
                    }
                }
                
                return $stats;
            } catch (\Exception $e) {
                return [
                    'hp' => 100,
                    'attack' => 50,
                    'defense' => 50,
                    'speed' => 50,
                ];
            }
        });
    }

    private function calculateDamage(array $attackerStats, array $defenderStats): int
    {
        // Formule simplifiée inspirée de Pokémon
        // Dégâts = ((Attaque de l'attaquant / Défense du défenseur) * 10) + variation aléatoire
        
        $baseDamage = ($attackerStats['attack'] / $defenderStats['defense']) * 15;
        
        // Ajouter une variation aléatoire de ±20%
        $randomFactor = 0.8 + (rand(0, 40) / 100);
        
        $damage = (int) ($baseDamage * $randomFactor);

        // Minimum 5 de dégâts, maximum 200
        return max(5, min(200, $damage));
    }
}
