#!/bin/bash
#
# HARelay Zero-Downtime Deployment Script
# Usage: sudo ./deploy.sh
#
# Uses symlink-based deployment for zero downtime:
# - Deploys to a new release directory
# - Shares .env and storage between releases
# - Atomically switches symlink when ready
# - Keeps last 5 releases for easy rollback
#

set -e

# Configuration
BASE_DIR="/var/www/harelay"
RELEASES_DIR="$BASE_DIR/releases"
SHARED_DIR="$BASE_DIR/shared"
CURRENT_LINK="$BASE_DIR/current"
APP_USER="www-data"
APP_GROUP="www-data"
PHP_VERSION="8.3"
BRANCH="main"
KEEP_RELEASES=5

# Git user for SSH - uses this user's SSH keys for git fetch
GIT_USER="deploy"

# Repository URL (SSH or HTTPS)
REPO_URL="git@github.com:matism/harelay.git"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    log_error "This script must be run as root (use sudo)"
    exit 1
fi

RELEASE_NAME=$(date +%Y%m%d_%H%M%S)
RELEASE_DIR="$RELEASES_DIR/$RELEASE_NAME"

log_info "Starting zero-downtime deployment..."
log_info "Release: $RELEASE_NAME"

# ==================== Setup directories ====================
log_step "Setting up directories..."

mkdir -p "$RELEASES_DIR"
mkdir -p "$SHARED_DIR/storage/app/public"
mkdir -p "$SHARED_DIR/storage/framework/cache"
mkdir -p "$SHARED_DIR/storage/framework/sessions"
mkdir -p "$SHARED_DIR/storage/framework/views"
mkdir -p "$SHARED_DIR/storage/logs"
mkdir -p "/var/www/.npm"
mkdir -p "/var/www/.config"

chown -R "$APP_USER:$APP_GROUP" "$SHARED_DIR"
chown -R "$APP_USER:$APP_GROUP" "/var/www/.npm"
chown -R "$APP_USER:$APP_GROUP" "/var/www/.config"

# ==================== Clone/copy release ====================
log_step "Creating new release..."

# If we have an existing release, copy it (faster than fresh clone)
if [[ -L "$CURRENT_LINK" ]] && [[ -d "$CURRENT_LINK" ]]; then
    CURRENT_RELEASE=$(readlink -f "$CURRENT_LINK")
    log_info "Copying from current release..."
    cp -r "$CURRENT_RELEASE" "$RELEASE_DIR"

    # Update from git
    cd "$RELEASE_DIR"

    # Fix git safe directory
    git config --global --add safe.directory "$RELEASE_DIR" 2>/dev/null || true

    if [[ -n "$GIT_USER" ]]; then
        sudo -u "$GIT_USER" git config --global --add safe.directory "$RELEASE_DIR" 2>/dev/null || true
        # Change ownership to GIT_USER for git operations (needs to write all files)
        chown -R "$GIT_USER:$GIT_USER" "$RELEASE_DIR"
        sudo -u "$GIT_USER" git fetch origin "$BRANCH"
        sudo -u "$GIT_USER" git reset --hard "origin/$BRANCH"
    else
        git fetch origin "$BRANCH"
        git reset --hard "origin/$BRANCH"
    fi
else
    # Fresh clone
    log_info "Fresh clone from repository..."
    if [[ -n "$GIT_USER" ]]; then
        sudo -u "$GIT_USER" git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR"
    else
        git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR"
    fi
fi

# Fix ownership for the release (back to APP_USER)
chown -R "$APP_USER:$APP_GROUP" "$RELEASE_DIR"

# ==================== Shared files ====================
log_step "Linking shared files..."

# Copy .env from shared if it doesn't exist there yet
if [[ ! -f "$SHARED_DIR/.env" ]]; then
    if [[ -f "$RELEASE_DIR/.env" ]]; then
        cp "$RELEASE_DIR/.env" "$SHARED_DIR/.env"
    elif [[ -f "$RELEASE_DIR/.env.example" ]]; then
        cp "$RELEASE_DIR/.env.example" "$SHARED_DIR/.env"
        log_warn "Created .env from .env.example - please configure it!"
    fi
    chown "$APP_USER:$APP_GROUP" "$SHARED_DIR/.env"
fi

# Remove release storage and link to shared
rm -rf "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
chown -h "$APP_USER:$APP_GROUP" "$RELEASE_DIR/storage"

# Link .env
rm -f "$RELEASE_DIR/.env"
ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
chown -h "$APP_USER:$APP_GROUP" "$RELEASE_DIR/.env"

# ==================== Install dependencies ====================
log_step "Installing PHP dependencies..."
cd "$RELEASE_DIR"
sudo -u "$APP_USER" composer install --no-dev --optimize-autoloader --no-interaction

log_step "Installing npm dependencies..."
rm -rf "$RELEASE_DIR/node_modules"
sudo -u "$APP_USER" npm install --no-audit --no-fund

log_step "Building frontend assets..."
sudo -u "$APP_USER" npm run build

# ==================== Laravel optimization ====================
log_step "Running database migrations..."
sudo -u "$APP_USER" php artisan migrate --force

log_step "Caching configuration..."
sudo -u "$APP_USER" php artisan config:cache
sudo -u "$APP_USER" php artisan route:cache
sudo -u "$APP_USER" php artisan view:cache

# ==================== Switch symlink (atomic!) ====================
log_step "Switching to new release (atomic)..."

# Create a temporary symlink and move it (atomic operation)
ln -sfn "$RELEASE_DIR" "$CURRENT_LINK.new"
mv -Tf "$CURRENT_LINK.new" "$CURRENT_LINK"

log_info "Symlink switched to: $RELEASE_DIR"

# ==================== Restart services ====================
log_step "Restarting services..."

# Reload PHP-FPM to clear opcache
systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || log_warn "php${PHP_VERSION}-fpm reload failed"

# Restart queue worker to pick up new code
systemctl restart harelay-queue 2>/dev/null || log_warn "harelay-queue restart failed"

# Restart tunnel server
systemctl restart harelay-tunnel 2>/dev/null || log_warn "harelay-tunnel restart failed"

# ==================== Cleanup old releases ====================
log_step "Cleaning up old releases..."

cd "$RELEASES_DIR"
RELEASE_COUNT=$(ls -1d */ 2>/dev/null | wc -l)

if [[ $RELEASE_COUNT -gt $KEEP_RELEASES ]]; then
    RELEASES_TO_DELETE=$((RELEASE_COUNT - KEEP_RELEASES))
    ls -1d */ | head -n $RELEASES_TO_DELETE | while read release; do
        log_info "Removing old release: $release"
        rm -rf "$RELEASES_DIR/$release"
    done
fi

# ==================== Done ====================
echo ""
log_info "Deployment completed successfully!"
echo ""
echo "Service Status:"
echo "---------------"
systemctl is-active --quiet harelay-tunnel && echo -e "harelay-tunnel: ${GREEN}running${NC}" || echo -e "harelay-tunnel: ${RED}stopped${NC}"
systemctl is-active --quiet harelay-queue && echo -e "harelay-queue:  ${GREEN}running${NC}" || echo -e "harelay-queue:  ${RED}stopped${NC}"
systemctl is-active --quiet "php${PHP_VERSION}-fpm" && echo -e "php${PHP_VERSION}-fpm:     ${GREEN}running${NC}" || echo -e "php${PHP_VERSION}-fpm:     ${RED}stopped${NC}"
echo ""
echo "Current release: $RELEASE_DIR"
echo "Kept releases: $KEEP_RELEASES"
echo ""

# ==================== Rollback instructions ====================
echo "To rollback to previous release:"
echo "  sudo ln -sfn /var/www/harelay/releases/PREVIOUS_RELEASE /var/www/harelay/current"
echo "  sudo systemctl reload php${PHP_VERSION}-fpm"
echo "  sudo systemctl restart harelay-queue harelay-tunnel"
