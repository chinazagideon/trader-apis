#!/bin/bash

set -e

# CRITICAL: Clear config cache BEFORE Laravel bootstraps
# Remove cached config file if it exists
rm -f bootstrap/cache/config.php 2>/dev/null || true

# Clear via artisan (in case it exists)
php artisan config:clear 2>/dev/null || true

# Wait for Redis - try both service name and container name
REDIS_FOUND=false
for host in redis trader-apis-redis; do
    for i in {1..10}; do
        if ping -c 1 "$host" > /dev/null 2>&1; then
            echo "✓ Redis reachable at: $host"
            REDIS_FOUND=true
            break 2
        fi
        sleep 1
    done
done

if [ "$REDIS_FOUND" = false ]; then
    echo "✗ Redis not reachable"
    exit 1
fi

# Ensure .env has correct Redis host (if using container name)
if ! grep -q "^REDIS_HOST=trader-apis-redis" .env 2>/dev/null; then
    sed -i 's/^REDIS_HOST=.*/REDIS_HOST=trader-apis-redis/' .env 2>/dev/null || true
    # Clear cache again after .env change
    rm -f bootstrap/cache/config.php 2>/dev/null || true
fi

# Final cache clear
php artisan cache:clear 2>/dev/null || true

exec php artisan queue:work redis \
    --queue=notifications,default,financial \
    --verbose \
    --tries=3 \
    --timeout=90
