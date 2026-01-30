#!/bin/bash
#
# HARelay Deployment Script
# Usage: sudo ./deploy.sh
#
# This script handles the full deployment process including:
# - Git pull with proper ownership handling
# - Composer and npm dependency installation
# - Database migrations
# - Cache clearing and rebuilding
# - Service restarts
#
# Git Authentication Options:
# 1. HTTPS: Set GIT_USER in this script, credentials cached with `git config --global credential.helper store`
# 2. SSH: Add deploy key for the user running this script (usually root)
#    - Generate: sudo ssh-keygen -t ed25519 -f /root/.ssh/id_ed25519
#    - Add public key to GitHub repo as deploy key
#

set -e

# Configuration
APP_DIR="/var/www/harelay"
APP_USER="www-data"
APP_GROUP="www-data"
PHP_VERSION="8.3"
BRANCH="main"

# Git user for SSH - uses this user's SSH keys for git fetch
GIT_USER="deploy"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    log_error "This script must be run as root (use sudo)"
    exit 1
fi

# Check if app directory exists
if [[ ! -d "$APP_DIR" ]]; then
    log_error "Application directory $APP_DIR does not exist"
    exit 1
fi

cd "$APP_DIR"

log_info "Starting HARelay deployment..."

# Fix git safe directory issue for all relevant users
log_info "Configuring git safe directory..."
git config --global --add safe.directory "$APP_DIR"
sudo -u "$APP_USER" git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
if [[ -n "$GIT_USER" ]]; then
    sudo -u "$GIT_USER" git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
fi

# Enable maintenance mode
log_info "Enabling maintenance mode..."
sudo -u "$APP_USER" php artisan down --retry=60 2>/dev/null || true

# Fix ownership BEFORE git pull (so deploy user can write all files)
if [[ -n "$GIT_USER" ]]; then
    log_info "Setting ownership for git operations..."
    chown -R "$GIT_USER:$GIT_USER" "$APP_DIR"
fi

# Pull latest changes
log_info "Pulling latest changes from $BRANCH..."
if [[ -n "$GIT_USER" ]]; then
    sudo -u "$GIT_USER" git fetch origin "$BRANCH"
    sudo -u "$GIT_USER" git reset --hard "origin/$BRANCH"
else
    git fetch origin "$BRANCH"
    git reset --hard "origin/$BRANCH"
fi

# Fix ownership after git pull
log_info "Fixing file ownership..."
chown -R "$APP_USER:$APP_GROUP" "$APP_DIR"

# Install PHP dependencies
log_info "Installing PHP dependencies..."
sudo -u "$APP_USER" composer install --no-dev --optimize-autoloader --no-interaction

# Install and build frontend assets
log_info "Cleaning node_modules and fixing npm cache..."
rm -rf "$APP_DIR/node_modules"
rm -rf "$APP_DIR/package-lock.json"

# Ensure node_modules doesn't exist and create fresh
if [[ -d "$APP_DIR/node_modules" ]]; then
    log_error "Failed to remove node_modules"
    exit 1
fi

# Fix npm cache ownership (common issue when npm was run as root)
# Create the directory if it doesn't exist, then fix ownership
mkdir -p "/var/www/.npm"
chown -R "$APP_USER:$APP_GROUP" "/var/www/.npm"

# Create node_modules with correct ownership
mkdir -p "$APP_DIR/node_modules"
chown "$APP_USER:$APP_GROUP" "$APP_DIR/node_modules"

log_info "Installing npm dependencies..."
sudo -u "$APP_USER" npm install --no-audit --no-fund

log_info "Building frontend assets..."
sudo -u "$APP_USER" npm run build

# Run database migrations
log_info "Running database migrations..."
sudo -u "$APP_USER" php artisan migrate --force

# Clear all caches
log_info "Clearing caches..."
sudo -u "$APP_USER" php artisan optimize:clear

# Rebuild caches
log_info "Rebuilding caches..."
sudo -u "$APP_USER" php artisan config:cache
sudo -u "$APP_USER" php artisan route:cache
sudo -u "$APP_USER" php artisan view:cache

# Fix permissions on storage and cache
log_info "Setting directory permissions..."
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"
chown -R "$APP_USER:$APP_GROUP" "$APP_DIR/storage"
chown -R "$APP_USER:$APP_GROUP" "$APP_DIR/bootstrap/cache"

# Restart services
log_info "Restarting tunnel server..."
systemctl restart harelay-tunnel 2>/dev/null || log_warn "harelay-tunnel service not found"

log_info "Restarting queue worker..."
systemctl restart harelay-queue 2>/dev/null || log_warn "harelay-queue service not found"

log_info "Reloading PHP-FPM..."
systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || log_warn "php${PHP_VERSION}-fpm service not found"

# Disable maintenance mode
log_info "Disabling maintenance mode..."
sudo -u "$APP_USER" php artisan up

# Show service status
log_info "Checking service status..."
echo ""
echo "Service Status:"
echo "---------------"
systemctl is-active --quiet harelay-tunnel && echo -e "harelay-tunnel: ${GREEN}running${NC}" || echo -e "harelay-tunnel: ${RED}stopped${NC}"
systemctl is-active --quiet harelay-queue && echo -e "harelay-queue:  ${GREEN}running${NC}" || echo -e "harelay-queue:  ${RED}stopped${NC}"
systemctl is-active --quiet "php${PHP_VERSION}-fpm" && echo -e "php${PHP_VERSION}-fpm:     ${GREEN}running${NC}" || echo -e "php${PHP_VERSION}-fpm:     ${RED}stopped${NC}"
systemctl is-active --quiet nginx && echo -e "nginx:          ${GREEN}running${NC}" || echo -e "nginx:          ${RED}stopped${NC}"
echo ""

log_info "Deployment completed successfully!"
