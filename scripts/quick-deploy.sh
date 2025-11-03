#!/bin/bash
# Minimal deployment script

cd /var/www/trader-apis || exit 1

# Permissions
chown -R 1000:1000 /var/www/trader-apis
mkdir -p storage/{app,framework/{sessions,views,cache},logs} bootstrap/cache

# Start services
docker-compose up -d

# Wait for DB
sleep 10

# Install deps
docker-compose exec -T app composer install --no-dev --optimize-autoloader --ignore-platform-reqs || true
chown -R 1000:1000 vendor

# Setup Laravel
docker-compose exec -T app php artisan key:generate --force 2>/dev/null || true
docker-compose exec -T app php artisan migrate --force 2>/dev/null || true
docker-compose exec -T app php artisan config:cache 2>/dev/null || true

echo "Done: http://$(hostname -I | awk '{print $1}'):8080"

