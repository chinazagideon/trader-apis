#!/bin/bash
# Proper migration order - runs User module first, then others

cd /var/www/trader-apis || exit 1

echo "Step 1: Running base migrations..."
docker compose exec -T app php artisan migrate:fresh --force

echo "Step 2: Running User module migrations first (creates users table)..."
docker compose exec -T app php artisan module:migrate User --force

echo "Step 3: Running all other module migrations..."
docker compose exec -T app php artisan module:migrate --force

echo "Step 4: Verifying users table exists..."
docker compose exec -T app php artisan tinker --execute="echo Schema::hasTable('users') ? '✓ users table exists' : '✗ users table missing';"

echo "Done!"

