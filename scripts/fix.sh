#!/bin/bash
# Quick fix for deployment issues

cd /var/www/trader-apis || exit 1

# Fix permissions (container uses UID 1000)
chown -R 1000:1000 /var/www/trader-apis
chmod -R 775 storage bootstrap/cache vendor 2>/dev/null || true
touch storage/logs/laravel.log && chmod 666 storage/logs/laravel.log

# Fix Git safe directory
docker-compose exec -T app git config --global --add safe.directory /var/www 2>/dev/null || true

docker-compose exec -T app php artisan migrate --force || true
docker-compose exec -T app php artisan module:seed User && docker-compose exec -T app php artisan module:seed Currency || true
docker-compose exec -T app php artisan module:seed --all || true

# Clear caches
docker-compose exec -T app php artisan optimize:clear 2>/dev/null || true

# Generate APP_KEY
if ! grep -q "APP_KEY=base64:" .env; then
    docker-compose exec -T app php artisan key:generate --force 2>/dev/null || true
fi

# Rebuild caches
docker-compose exec -T app php artisan config:cache 2>/dev/null || true

# Restart services
docker-compose restart app queue

echo "Fix complete. Check: docker-compose ps"

