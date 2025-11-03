#!/bin/bash

###############################################################################
# Troubleshooting Script for Deployment Issues
###############################################################################
# This script helps diagnose and fix common deployment issues
# Usage: ./scripts/troubleshoot.sh
###############################################################################

set -e

# Colors
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
    log_info "Please set APP_DIR or navigate to the application directory"
    exit 1
}

log_info "Starting troubleshooting for ${APP_DIR}..."

# Check Docker
log_info "Checking Docker..."
if ! command -v docker &> /dev/null; then
    log_error "Docker is not installed"
    exit 1
fi
log_success "Docker is installed: $(docker --version)"

# Check Docker Compose
log_info "Checking Docker Compose..."
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
    log_success "Using docker-compose"
elif docker compose version &> /dev/null; then
    COMPOSE_CMD="docker compose"
    log_success "Using docker compose (new syntax)"
else
    log_error "Docker Compose is not installed"
    exit 1
fi

# Check if containers are running
log_info "Checking container status..."
$COMPOSE_CMD ps

# Check .env file
log_info "Checking .env file..."
if [ ! -f ".env" ]; then
    log_error ".env file is missing!"
    if [ -f ".env.example" ]; then
        log_info "Creating .env from .env.example..."
        cp .env.example .env
        log_warning "Please update .env with your configuration"
    else
        log_error ".env.example not found. Please create .env manually"
    fi
else
    log_success ".env file exists"
    if ! grep -q "APP_KEY=base64:" .env; then
        log_warning "APP_KEY is not set in .env"
    fi
fi

# Check permissions
log_info "Checking file permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage 2>/dev/null || log_warning "Could not set storage permissions"
    log_success "Storage permissions checked"
else
    log_error "Storage directory missing!"
    mkdir -p storage/{app,framework,logs}
    mkdir -p storage/framework/{sessions,views,cache}
    chmod -R 775 storage
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache 2>/dev/null || log_warning "Could not set cache permissions"
    log_success "Cache permissions checked"
else
    log_error "Bootstrap cache directory missing!"
    mkdir -p bootstrap/cache
    chmod -R 775 bootstrap/cache
fi

# Check if containers are healthy
log_info "Checking container health..."

# Check MySQL
log_info "Testing MySQL connection..."
if $COMPOSE_CMD exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; then
    log_success "MySQL is running and accessible"
else
    log_error "MySQL is not responding"
    log_info "MySQL logs:"
    $COMPOSE_CMD logs --tail=20 db
fi

# Check Redis
log_info "Testing Redis connection..."
if $COMPOSE_CMD exec -T redis redis-cli ping 2>/dev/null | grep -q PONG; then
    log_success "Redis is running and accessible"
else
    log_error "Redis is not responding"
    log_info "Redis logs:"
    $COMPOSE_CMD logs --tail=20 redis
fi

# Check App container
log_info "Checking app container..."
if $COMPOSE_CMD ps app | grep -q "Up"; then
    log_success "App container is running"

    # Test PHP
    log_info "Testing PHP..."
    if $COMPOSE_CMD exec -T app php -v &>/dev/null; then
        log_success "PHP is working"
    else
        log_error "PHP is not working"
    fi

    # Test Composer
    log_info "Testing Composer..."
    if $COMPOSE_CMD exec -T app composer --version &>/dev/null; then
        log_success "Composer is available"
    else
        log_error "Composer is not available"
    fi

    # Check if vendor exists
    log_info "Checking Composer dependencies..."
    if [ ! -d "vendor" ]; then
        log_warning "vendor directory missing, installing dependencies..."
        $COMPOSE_CMD exec -T app composer install --no-dev --optimize-autoloader
    else
        log_success "Composer dependencies installed"
    fi

    # Test Laravel
    log_info "Testing Laravel artisan..."
    if $COMPOSE_CMD exec -T app php artisan --version &>/dev/null; then
        log_success "Laravel is working"

        # Check database connection
        log_info "Testing database connection from Laravel..."
        if $COMPOSE_CMD exec -T app php artisan db:show &>/dev/null; then
            log_success "Database connection successful"
        else
            log_error "Database connection failed"
            log_info "Attempting to check connection..."
            $COMPOSE_CMD exec -T app php artisan tinker --execute="echo DB::connection()->getPdo() ? 'Connected' : 'Failed';"
        fi
    else
        log_error "Laravel artisan is not working"
        log_info "App container logs:"
        $COMPOSE_CMD logs --tail=30 app
    fi
else
    log_error "App container is not running"
    log_info "App container logs:"
    $COMPOSE_CMD logs --tail=30 app
fi

# Check Nginx
log_info "Checking Nginx..."
if $COMPOSE_CMD ps nginx | grep -q "Up"; then
    log_success "Nginx is running"

    # Test HTTP endpoint
    log_info "Testing HTTP endpoint..."
    if curl -s http://localhost:8080/up &>/dev/null; then
        log_success "Application is accessible via HTTP"
    else
        log_warning "Application is not responding on port 8080"
        log_info "Nginx logs:"
        $COMPOSE_CMD logs --tail=20 nginx
    fi
else
    log_error "Nginx is not running"
    log_info "Nginx logs:"
    $COMPOSE_CMD logs --tail=20 nginx
fi

# Check Queue worker
log_info "Checking Queue worker..."
if $COMPOSE_CMD ps queue | grep -q "Up"; then
    log_success "Queue worker is running"
else
    log_warning "Queue worker is not running"
    log_info "Queue logs:"
    $COMPOSE_CMD logs --tail=20 queue
fi

# Summary
echo ""
log_info "============================================================================"
log_info "Troubleshooting Summary"
log_info "============================================================================"
log_info "To view logs: $COMPOSE_CMD logs -f [service]"
log_info "To restart all: $COMPOSE_CMD restart"
log_info "To rebuild: $COMPOSE_CMD build --no-cache"
log_info "To restart app: $COMPOSE_CMD restart app"
log_info "============================================================================"

