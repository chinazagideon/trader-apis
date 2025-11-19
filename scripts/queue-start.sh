#!/bin/bash

set -e

# Get Redis IP from Docker
REDIS_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' trader-apis-redis 2>/dev/null)

if [ -n "$REDIS_IP" ]; then
    # Temporarily override REDIS_HOST
    export REDIS_HOST="$REDIS_IP"
    echo "Using Redis IP: $REDIS_IP"
fi

php artisan config:clear 2>/dev/null || true

exec php artisan queue:work redis \
    --queue=notifications,default,financial \
    --verbose \
    --tries=3 \
    --timeout=90
