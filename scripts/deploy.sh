#!/bin/bash

###############################################################################
# Laravel Trader APIs - Production Deployment Script
###############################################################################
# This script automates the deployment of the Laravel application to a server
# Usage: ./scripts/deploy.sh [environment]
# Example: ./scripts/deploy.sh production
###############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="trader-apis"
APP_DIR="/var/www/${APP_NAME}"
GIT_REPO="https://github.com/chinazagideon/trader-apis.git"
GIT_BRANCH="${DEPLOY_BRANCH:-main}"
ENVIRONMENT="${1:-production}"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    log_error "Please run as root (use sudo)"
    exit 1
fi

log_info "Starting deployment for ${APP_NAME}..."
log_info "Environment: ${ENVIRONMENT}"
log_info "Branch: ${GIT_BRANCH}"

# Step 1: Install required dependencies
log_info "Step 1: Checking system dependencies..."
install_dependencies() {
    if ! command -v docker &> /dev/null; then
        log_info "Installing Docker..."
        curl -fsSL https://get.docker.com -o get-docker.sh
        sh get-docker.sh
        rm get-docker.sh
        systemctl enable docker
        systemctl start docker
    else
        log_success "Docker is already installed"
    fi

    if ! command -v docker-compose &> /dev/null; then
        log_info "Installing Docker Compose..."
        curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
    else
        log_success "Docker Compose is already installed"
    fi

    if ! command -v git &> /dev/null; then
        log_info "Installing Git..."
        apt-get update
        apt-get install -y git
    else
        log_success "Git is already installed"
    fi
}
install_dependencies

# Step 2: Create application directory
log_info "Step 2: Setting up application directory..."
mkdir -p ${APP_DIR}
cd ${APP_DIR}

# Step 3: Clone or update repository
log_info "Step 3: Cloning/updating repository..."
if [ -d ".git" ]; then
    log_info "Repository exists, updating..."
    git fetch origin
    git checkout ${GIT_BRANCH}
    git pull origin ${GIT_BRANCH}
    log_success "Repository updated"
else
    log_info "Cloning repository..."
    git clone -b ${GIT_BRANCH} ${GIT_REPO} .
    log_success "Repository cloned"
fi

# Step 4: Setup environment file
log_info "Step 4: Setting up environment configuration..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        log_warning ".env file created from .env.example. Please update it with your configuration!"
    else
        log_error ".env.example not found. Creating basic .env file..."
        cat > .env << EOF
APP_NAME="${APP_NAME}"
APP_ENV=${ENVIRONMENT}
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=trader_apis
DB_USERNAME=trader
DB_PASSWORD=trader

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
EOF
        log_warning "Basic .env created. Please update it with your configuration!"
    fi
else
    log_success ".env file already exists"
fi

# Step 5: Set proper permissions
log_info "Step 5: Setting up file permissions..."
# Create necessary directories if they don't exist
mkdir -p ${APP_DIR}/storage/{app,framework/{sessions,views,cache},logs}
mkdir -p ${APP_DIR}/bootstrap/cache

chown -R www-data:www-data ${APP_DIR} 2>/dev/null || chown -R 1000:1000 ${APP_DIR}
chmod -R 755 ${APP_DIR}
chmod -R 775 ${APP_DIR}/storage
chmod -R 775 ${APP_DIR}/bootstrap/cache
log_success "Permissions set"

# Step 6: Build and start Docker containers
log_info "Step 6: Building Docker containers..."

# Check if docker-compose or docker compose is available
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
elif docker compose version &> /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
else
    log_error "Docker Compose is not available"
    exit 1
fi

${COMPOSE_CMD} down 2>/dev/null || true
${COMPOSE_CMD} build --no-cache
log_success "Docker images built"

# Step 7: Start services
log_info "Step 7: Starting Docker services..."
${COMPOSE_CMD} up -d
log_success "Docker services started"

# Step 8: Wait for services to be ready
log_info "Step 8: Waiting for services to be ready..."
sleep 10

# Check if MySQL is ready
log_info "Waiting for MySQL..."
for i in {1..60}; do
    if ${COMPOSE_CMD} exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; then
        log_success "MySQL is ready"
        break
    fi
    if [ $i -eq 60 ]; then
        log_error "MySQL failed to start after 2 minutes"
        log_info "MySQL logs:"
        ${COMPOSE_CMD} logs --tail=30 db
        exit 1
    fi
    sleep 2
done

# Check if Redis is ready
log_info "Waiting for Redis..."
for i in {1..30}; do
    if ${COMPOSE_CMD} exec -T redis redis-cli ping 2>/dev/null | grep -q PONG; then
        log_success "Redis is ready"
        break
    fi
    if [ $i -eq 30 ]; then
        log_error "Redis failed to start"
        log_info "Redis logs:"
        ${COMPOSE_CMD} logs --tail=30 redis
        exit 1
    fi
    sleep 2
done

# Step 9: Wait for app container to be ready
log_info "Step 9: Waiting for app container to be ready..."
for i in {1..30}; do
    if ${COMPOSE_CMD} exec -T app php -v &>/dev/null; then
        log_success "App container is ready"
        break
    fi
    if [ $i -eq 30 ]; then
        log_error "App container failed to start"
        log_info "App container logs:"
        ${COMPOSE_CMD} logs --tail=30 app
        exit 1
    fi
    sleep 2
done

# Step 10: Install Composer dependencies
log_info "Step 10: Installing Composer dependencies..."
${COMPOSE_CMD} exec -T app composer install --no-dev --optimize-autoloader || {
    log_error "Composer install failed"
    log_info "Trying with --ignore-platform-reqs..."
    ${COMPOSE_CMD} exec -T app composer install --no-dev --optimize-autoloader --ignore-platform-reqs
}
log_success "Composer dependencies installed"

# Step 11: Generate application key if not set
log_info "Step 11: Generating application key..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    ${COMPOSE_CMD} exec -T app php artisan key:generate --force || {
        log_warning "Failed to generate key in container, trying directly..."
        APP_KEY=$(docker run --rm ${COMPOSE_CMD} config 2>/dev/null | grep "image:" | head -1 | awk '{print $2}' || echo "")
        if [ -z "$APP_KEY" ]; then
            ${COMPOSE_CMD} exec -T app php artisan key:generate --force
        fi
    }
    log_success "Application key generated"
else
    log_success "Application key already exists"
fi

# Step 12: Clear and prepare caches
log_info "Step 12: Clearing old caches..."
${COMPOSE_CMD} exec -T app php artisan optimize:clear || true
${COMPOSE_CMD} exec -T app php artisan config:clear || true
log_success "Caches cleared"

# Step 13: Run migrations
log_info "Step 13: Running database migrations..."
${COMPOSE_CMD} exec -T app php artisan migrate --force || {
    log_error "Migrations failed"
    log_info "Checking database connection..."
    ${COMPOSE_CMD} exec -T app php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB Connected'; } catch(Exception \$e) { echo 'DB Error: ' . \$e->getMessage(); }"
    exit 1
}
log_success "Migrations completed"

# Step 14: Run seeders (optional, comment out for production)
# log_info "Step 14: Running database seeders..."
# ${COMPOSE_CMD} exec -T app php artisan db:seed --force
# log_success "Seeders completed"

# Step 15: Optimize application
log_info "Step 15: Optimizing application..."
${COMPOSE_CMD} exec -T app php artisan config:cache || log_warning "Config cache failed"
${COMPOSE_CMD} exec -T app php artisan route:cache || log_warning "Route cache failed"
${COMPOSE_CMD} exec -T app php artisan view:cache || log_warning "View cache failed"
${COMPOSE_CMD} exec -T app php artisan event:cache || log_warning "Event cache failed"
log_success "Application optimized"

# Step 16: Restart queue worker
log_info "Step 16: Restarting queue worker..."
${COMPOSE_CMD} restart queue || log_warning "Queue restart failed"
log_success "Queue worker restarted"

# Step 17: Show status
log_info "Step 17: Checking service status..."
${COMPOSE_CMD} ps

log_success "============================================================================"
log_success "Deployment completed successfully!"
log_success "============================================================================"
log_info "Application URL: http://$(hostname -I | awk '{print $1}'):8080"
log_info "To view logs: ${COMPOSE_CMD} -f ${APP_DIR}/docker-compose.yml logs -f"
log_info "To stop services: ${COMPOSE_CMD} -f ${APP_DIR}/docker-compose.yml down"
log_info "To restart services: ${COMPOSE_CMD} -f ${APP_DIR}/docker-compose.yml restart"
log_info "To troubleshoot: cd ${APP_DIR} && ./scripts/troubleshoot.sh"
log_success "============================================================================"


