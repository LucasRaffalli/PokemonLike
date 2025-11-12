# 🎮 PokemonLikeSymf

## Description
A Pokémon-like game built with Symfony 7.3, featuring team management, real-time PvP battles, and bot opponents.

## 📋 Features
- 🔐 **Authentication**: Registration, login, profile management
- 📖 **Pokédex**: Browse all Pokémon via PokéAPI
- 👥 **Team Management**: Build your team (max 6 unique Pokémon)
- ⚔️ **PvP Battle System**: Turn-based combat with real stats
- 🤖 **Bot Opponents**: Fight against AI with random teams
- 📊 **Statistics**: Track your wins/losses

## Prerequisites
- PHP 8.2+
- Composer
- Symfony CLI
- Node.js & npm
- MySQL 8.0+

## Installation

```bash
# Clone the repository
git clone https://github.com/LucasRaffalli/PokemonLike.git
cd PokemonLikeSymf

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Configure environment
cp .env .env.local
# Edit .env.local with your database credentials and API URL
# DATABASE_URL="mysql://root:@localhost:3306/pokemonlike"
# APP_SECRET=your_secret_key_here
# POKEMON_API_URL=https://pokeapi.co/api/v2

# Create database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## 🎮 Custom Commands

### Create Bot Opponents
```bash
php bin/console app:create-bots
```
Creates 5 bot opponents with random Pokémon teams:
- Sacha_BOT
- Ondine_BOT
- Pierre_BOT
- Flora_BOT
- Red_BOT

### Update Pokémon Names
```bash
php bin/console app:update-pokemon-names
```
Fetches and updates Pokémon names from PokéAPI for all team Pokémon in the database.

## Development

```bash
# Start Symfony server
symfony server:start
# or
php -S localhost:8000 -t public

# Watch and compile assets
npm run watch

# Clear cache
php bin/console cache:clear
```

## Build for Production

```bash
npm run build
```

## 🎯 Main Routes
- `/` - Homepage
- `/register` - User registration
- `/login` - User login
- `/dashboard` - User dashboard
- `/pokedex` - Pokémon list
- `/pokedex/{id}` - Pokémon details
- `/team` - Team management
- `/battle` - Battle arena
- `/profile` - Profile settings

## 🔒 Security Features
- ✅ CSRF protection on all forms
- ✅ Password hashing (bcrypt/argon2)
- ✅ Role-based access control
- ✅ Server-side token validation

## 👤 Default Bot Accounts
- **Email**: sacha@bot.com (or any other bot)
- **Password**: bot123

## License
MIT