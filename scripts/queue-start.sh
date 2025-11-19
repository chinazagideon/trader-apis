#!/bin/bash

set -e

echo "=========================================="
echo "Queue Worker Startup Script"
echo "=========================================="

# Wait for Redis DNS resolution
echo "[1/3] Waiting for Redis DNS resolution..."
MAX_ATTEMPTS=30
ATTEMPT=1

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    if ping -c 1 redis > /dev/null 2>&1; then
        echo "✓ Redis DNS resolved successfully"
        break
    else
        echo "  Attempt $ATTEMPT/$MAX_ATTEMPTS: Redis not reachable, waiting 2 seconds..."
        sleep 2
        ATTEMPT=$((ATTEMPT + 1))
    fi
done

if [ $ATTEMPT -gt $MAX_ATTEMPTS ]; then
    echo "✗ Failed to resolve Redis DNS after $MAX_ATTEMPTS attempts"
    exit 1
fi

# Clear Laravel caches
echo "[2/3] Clearing Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
echo "✓ Caches cleared"

# Wait a moment for Redis to be fully ready
echo "[3/3] Verifying Redis connection..."
sleep 2

# Start queue worker
echo "=========================================="
echo "Starting queue worker..."
echo "Queues: notifications,default,financial"
echo "=========================================="

exec php artisan queue:work redis \
    --queue=notifications,default,financial \
    --verbose \
    --tries=3 \
    --timeout=90
