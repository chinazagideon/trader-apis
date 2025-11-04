#!/bin/bash
# Proper migration order - runs User module first, then others
# Run this script from the HOST server, not inside the container

APP_DIR="${1:-/var/www/trader-apis}"
cd "$APP_DIR" || exit 1

# Detect compose command
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
else
    echo "Error: Docker Compose not found"
    exit 1
fi

echo "Step 1: Running base migrations..."
$COMPOSE_CMD exec -T app php artisan migrate:fresh --force

echo "Step 2: Running User module migrations first (creates users table)..."
$COMPOSE_CMD exec -T app php artisan module:migrate User --force

echo "Step 3: Running all other module migrations..."
$COMPOSE_CMD exec -T app php artisan module:migrate --force

echo "Step 4: Verifying users table exists..."
$COMPOSE_CMD exec -T app php artisan tinker --execute="echo Schema::hasTable('users') ? '✓ users table exists' : '✗ users table missing';"

echo "Done!"

