# PokemonLikeSymf

## Description
A Pokémon-like game built with Symfony.

## Prerequisites
- PHP 8.1+
- Composer
- Symfony CLI
- Node.js & npm

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd PokemonLikeSymf

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Configure environment
cp .env .env.local
# Edit .env.local with your database credentials

# Create database (database name: pokemon_like)
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Development

```bash
# Start Symfony server
symfony server:start

# Watch and compile assets
npm run watch
```

## Build for Production

```bash
npm run build
```

## License
MIT