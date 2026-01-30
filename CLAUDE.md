# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**HARelay** - A Laravel 12 application providing secure remote access to Home Assistant. Users get unique subdomains (e.g., `abc123.harelay.com`) and install a lightweight HA add-on that establishes a WebSocket tunnel for proxying requests.

### Key Components

- **Marketing Site**: Landing page, pricing, how-it-works
- **User Dashboard**: Connection management, setup guide, settings
- **Tunnel Server**: Laravel Reverb WebSocket + HTTP API for add-on communication
- **Proxy System**: Routes subdomain requests through WebSocket tunnel to Home Assistant

## Common Commands

```bash
# Development (runs server, queue, logs, vite, and reverb in parallel)
composer dev

# Setup new environment
composer setup

# Run tests
composer test

# Run single test
php artisan test --filter=TestName

# Code formatting
./vendor/bin/pint

# Database migrations
php artisan migrate

# Create migration
php artisan make:migration create_table_name

# Create model with migration, factory, and controller
php artisan make:model ModelName -mfc

# Start only the Reverb WebSocket server
php artisan reverb:start

# Clear all caches
php artisan optimize:clear
```

## Architecture

- **Framework**: Laravel 12 with Vite and Tailwind CSS 4
- **Authentication**: Laravel Breeze (Blade)
- **WebSocket**: Laravel Reverb (Pusher-compatible)
- **Database**: MySQL (production), SQLite (development/testing)
- **Queue**: Database driver
- **Cache/Session**: Database driver
- **Broadcasting**: Reverb

### Database Tables

- `users` - User accounts (Breeze)
- `ha_connections` - User's HA connection (subdomain, token, status)
- `subscriptions` - User subscription plans

### Directory Structure

```
app/
├── Events/                    # Tunnel events (TunnelRequest, TunnelConnected, etc.)
├── Http/
│   ├── Controllers/
│   │   ├── Api/               # Tunnel API for HA add-on
│   │   ├── DashboardController.php
│   │   ├── ConnectionController.php
│   │   ├── ProxyController.php    # Handles subdomain proxying
│   │   └── MarketingController.php
│   └── Middleware/
│       ├── ProxyMiddleware.php    # Subdomain detection
│       └── CheckSubscription.php
├── Models/
│   ├── User.php
│   ├── HaConnection.php
│   └── Subscription.php
└── Services/
    └── TunnelManager.php      # Orchestrates tunnel communication

routes/
├── api.php                    # Tunnel API routes (/api/tunnel/*)
├── channels.php               # WebSocket channel auth
└── web.php                    # Web + subdomain proxy routes

resources/views/
├── dashboard/                 # User dashboard views
├── marketing/                 # Public marketing pages
├── errors/                    # Tunnel error pages
└── components/                # Blade components
```

## Tunnel System

### Request Flow

1. User visits `subdomain.harelay.com/path`
2. `ProxyController` receives request
3. `TunnelManager::proxyRequest()` broadcasts `TunnelRequest` event via Reverb
4. HA add-on receives event on `private-tunnel.{subdomain}` channel
5. Add-on makes request to local HA and POSTs response to `/api/tunnel/response`
6. `TunnelManager` retrieves response from cache and returns to user

### API Endpoints (for HA Add-on)

| Endpoint | Purpose |
|----------|---------|
| `POST /api/tunnel/connect` | Register add-on connection |
| `POST /api/tunnel/disconnect` | Unregister connection |
| `POST /api/tunnel/heartbeat` | Keep-alive (every 30-60s) |
| `POST /api/tunnel/auth` | WebSocket channel authentication |
| `POST /api/tunnel/response` | Submit proxied response |
| `POST /api/tunnel/poll` | Polling fallback for requests |

### WebSocket Events

- **Channel**: `private-tunnel.{subdomain}`
- **Event**: `tunnel.request` - Contains request_id, method, uri, headers, body

## Testing

Tests use in-memory SQLite. Run with `composer test` or `php artisan test`.

### Testing the Tunnel Locally

```bash
# 1. Create a test user and connection
php artisan tunnel:create-test
# Output: test@example.com / password, subdomain, and token

# 2. Run the tunnel simulator (simulates HA add-on)
php artisan tunnel:simulate {subdomain} "{token}" --ha-url=http://localhost:8123

# 3. Add to /etc/hosts: 127.0.0.1 {subdomain}.harelay.com
# 4. Visit http://{subdomain}.harelay.com:8000 and log in
```

### Testing with Valet

```bash
# Link with custom domain
valet link harelay --secure

# Update .env
APP_PROXY_DOMAIN=harelay.test
APP_URL=https://harelay.test
SESSION_DOMAIN=.harelay.test

# Run Reverb separately (Valet handles PHP only)
php artisan reverb:start
```

Valet automatically handles wildcard subdomains: `https://{subdomain}.harelay.test`

### HA Add-on

The Home Assistant add-on is in a separate repository at `../harelay-addon/`. See that project's README for development and deployment instructions.

## Environment Variables

Key variables to configure:

```env
APP_PROXY_DOMAIN=harelay.com          # Domain for subdomains
BROADCAST_CONNECTION=reverb           # Use Reverb for WebSocket
REVERB_HOST=localhost                 # WebSocket server host
REVERB_PORT=8080                      # WebSocket server port
REVERB_SCHEME=http                    # http or https
```

## Security Notes

- Connection tokens are hashed (bcrypt) in database
- Tokens shown only once to user (on create/regenerate)
- Users must be authenticated to access their subdomain
- Owner verification on all proxy requests
- Subdomain routes include anti-crawling headers (X-Robots-Tag, X-Frame-Options)
- Rate limiting on API endpoints (60 requests/minute)
- Security headers on proxy responses (CSP, X-Content-Type-Options)

## Working Guidelines

- **Always update documentation**: When adding features, commands, or changing behavior, update both `README.md` and `CLAUDE.md`
- **Run tests before committing**: Use `composer test` to verify changes
- **Format code**: Run `./vendor/bin/pint` before committing
