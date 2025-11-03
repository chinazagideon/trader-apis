#!/bin/bash

###############################################################################
# Quick Fix Script for Common Deployment Issues
###############################################################################
# This script fixes the most common deployment problems
# Usage: ./scripts/fix-common-issues.sh
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="${APP_DIR:-/var/www/trader-apis}"

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

cd "${APP_DIR}" 2>/dev/null || {
    log_error "Application directory not found: ${APP_DIR}"
    exit 1
}

# Detect compose command
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
else
    log_error "Docker Compose not found"
    exit 1
fi

log_info "Fixing common deployment issues..."

# Fix 1: Create missing directories
log_info "Fix 1: Creating missing directories..."
mkdir -p storage/{app,framework/{sessions,views,cache},logs}
mkdir -p bootstrap/cache
log_success "Directories created"

# Fix 2: Fix permissions
log_info "Fix 2: Fixing file permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || log_warning "Permission fix may need sudo"
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || 
chown -R 1000:1000 storage bootstrap/cache 2>/dev/null || log_warning "Ownership fix may need sudo"
log_success "Permissions fixed"

# Fix 3: Ensure .env exists
log_info "Fix 3: Checking .env file..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        log_success ".env created from .env.example"
    else
        log_error ".env.example not found. Please create .env manually"
    fi
fi

# Fix 4: Generate APP_KEY if missing
if [ -f ".env" ]; then
    if ! grep -q "APP_KEY=base64:" .env; then
        log_info "Fix 4: Generating APP_KEY..."
        if ${COMPOSE_CMD} ps app | grep -q "Up"; then
            ${COMPOSE_CMD} exec -T app php artisan key:generate --force
            log_success "APP_KEY generated"
        else
            log_warning "App container not running. Key will be generated on next deployment"
        fi
    fi
fi

# Fix 5: Stop and restart containers
log_info "Fix 5: Restarting containers..."
${COMPOSE_CMD} down 2>/dev/null || true
${COMPOSE_CMD} up -d
log_success "Containers restarted"

# Fix 6: Wait for services
log_info "Fix 6: Waiting for services to be ready..."
sleep 15

# Fix 7: Install dependencies if vendor missing
if [ ! -d "vendor" ] && ${COMPOSE_CMD} ps app | grep -q "Up"; then
    log_info "Fix 7: Installing Composer dependencies..."
    ${COMPOSE_CMD} exec -T app composer install --no-dev --optimize-autoloader || true
    log_success "Dependencies installed"
fi

# Fix 8: Clear and rebuild caches
if ${COMPOSE_CMD} ps app | grep -q "Up"; then
    log_info "Fix 8: Clearing and rebuilding caches..."
    ${COMPOSE_CMD} exec -T app php artisan optimize:clear || true
    ${COMPOSE_CMD} exec -T app php artisan config:cache || true
    ${COMPOSE_CMD} exec -T app php artisan route:cache || true
    log_success "Caches rebuilt"
fi

log_success "============================================================================"
log_success "Common issues fixed!"
log_success "============================================================================"
log_info "Run ./scripts/troubleshoot.sh to verify everything is working"
log_info "View logs: ${COMPOSE_CMD} logs -f"

