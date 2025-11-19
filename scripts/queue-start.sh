#!/bin/bash

set -e

# Get Redis IP from Docker (if docker command available)
REDIS_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' trader-apis-redis 2>/dev/null || echo "")

if [ -n "$REDIS_IP" ]; then
    # Temporarily override REDIS_HOST
    export REDIS_HOST="$REDIS_IP"
    echo "Using Redis IP: $REDIS_IP"
else
    # Fallback: try container name
    export REDIS_HOST="trader-apis-redis"
    echo "Using Redis hostname: trader-apis-redis"
fi

# Clear config cache
php artisan config:clear 2>/dev/null || true
rm -f bootstrap/cache/config.php 2>/dev/null || true

# Run queue worker directly (we're already inside the container)
exec php artisan queue:work redis \
    --queue=notifications,default,financial \
    --verbose \
    --tries=3 \
    --timeout=90
