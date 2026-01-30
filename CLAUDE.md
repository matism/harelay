# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**HARelay** - A Laravel 12 application providing secure remote access to Home Assistant. Users get unique subdomains (e.g., `abc123.harelay.com`) and install a lightweight HA add-on that establishes a WebSocket tunnel for proxying requests.

### Key Components

- **Marketing Site**: Landing page, pricing, how-it-works
- **User Dashboard**: Connection management, setup guide, settings
- **Tunnel Server**: Workerman-based WebSocket server (`tunnel-server.php`)
- **Proxy System**: Routes subdomain requests through WebSocket tunnel to Home Assistant

## Common Commands

```bash
# Development (runs server, queue, logs, vite, and tunnel server in parallel)
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

# Start tunnel server manually (usually run via composer dev)
php tunnel-server.php start

# Clear all caches
php artisan optimize:clear
```

## Architecture

- **Framework**: Laravel 12 with Vite and Tailwind CSS 4
- **Authentication**: Laravel Breeze (Blade)
- **Tunnel Server**: Workerman WebSocket (ports 8081 + 8082)
- **Database**: MySQL (production), SQLite (development/testing)
- **Queue**: Database driver
- **Cache/Session**: Database driver (file cache for tunnel IPC)

### Database Tables

- `users` - User accounts (Breeze)
- `ha_connections` - User's HA connection (subdomain, token, status, last_connected_at)
- `subscriptions` - User subscription plans

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Dashboard + subscription views
│   │   ├── ConnectionController.php   # Create/delete/regenerate token
│   │   ├── ProxyController.php        # HTTP proxying + WS script injection
│   │   ├── MarketingController.php    # Public pages
│   │   └── ProfileController.php      # User profile (Breeze)
│   └── Middleware/
│       ├── SubdomainProxy.php         # Subdomain detection (local dev)
│       ├── ProxySecurityHeaders.php   # Security headers for proxied responses
│       └── CheckSubscription.php
├── Models/
│   ├── User.php
│   ├── HaConnection.php               # Has getProxyUrl() helper
│   └── Subscription.php
├── Services/
│   └── TunnelManager.php              # File-cache IPC with tunnel server
└── Console/Commands/
    └── CreateTestConnection.php       # Creates test user/connection

tunnel-server.php                      # Workerman WebSocket tunnel server

routes/
├── web.php                            # Web routes + subdomain proxy
├── api.php                            # Empty (all tunnel communication via WebSocket)
└── auth.php                           # Auth routes (Breeze)

resources/views/
├── dashboard/                         # User dashboard views
├── marketing/                         # Public marketing pages
├── errors/                            # Tunnel error pages (auth-required, disconnected, timeout)
└── components/                        # Blade components
```

## Tunnel System

### Architecture

The tunnel uses a Workerman-based WebSocket server that runs alongside Laravel:

- **Port 8081**: Add-on connections (authentication, HTTP request/response relay)
- **Port 8082**: Browser WebSocket proxy (for Home Assistant real-time features)

Communication between Laravel web requests and the tunnel server uses file-based cache (`Cache::store('file')`) to avoid database connection issues in long-running processes.

### Request Flow (HTTP)

1. User visits `subdomain.harelay.com/path`
2. `SubdomainProxy` middleware detects subdomain, calls `ProxyController`
3. `ProxyController` verifies auth and ownership
4. `TunnelManager::proxyRequest()` stores request in file cache
5. `tunnel-server.php` polls cache, sends request to add-on via WebSocket
6. Add-on forwards to Home Assistant, returns response via WebSocket
7. `tunnel-server.php` stores response in file cache
8. `TunnelManager` retrieves response and returns to user

### Request Flow (WebSocket)

1. Browser loads HA page with injected WebSocket proxy script
2. Script intercepts `new WebSocket()` calls to `/api/websocket`
3. Browser connects to port 8082 (dev) or /wss path (prod) with subdomain authentication
4. `tunnel-server.php` tells add-on to open WebSocket to HA
5. Messages are relayed bidirectionally through the tunnel

### Add-on Protocol

The HA add-on (`../harelay-addon/`) connects via WebSocket to port 8081:

**Authentication:**
```json
{"type": "auth", "subdomain": "abc123", "token": "secret"}
→ {"type": "auth_result", "success": true, "subdomain": "abc123"}
```

**HTTP Request/Response:**
```json
← {"type": "request", "request_id": "uuid", "method": "GET", "uri": "/", "headers": {}, "body": null}
→ {"type": "response", "request_id": "uuid", "status_code": 200, "headers": {}, "body": "base64..."}
```

**WebSocket Proxy:**
```json
← {"type": "ws_open", "stream_id": "id", "path": "/api/websocket"}
← {"type": "ws_message", "stream_id": "id", "message": "..."}
→ {"type": "ws_message", "stream_id": "id", "message": "..."}
← {"type": "ws_close", "stream_id": "id"}
```

**Heartbeat:**
```json
→ {"type": "heartbeat"}
← {"type": "pong"}
```

## Testing

Tests use in-memory SQLite. Run with `composer test` or `php artisan test`.

### Testing the Tunnel Locally

```bash
# 1. Create a test user and connection
php artisan tunnel:create-test
# Output: test@example.com / password, subdomain, and token

# 2. Start the development servers
composer dev

# 3. Configure the HA add-on with subdomain and token
# See ../harelay-addon/ for add-on setup

# 4. Add to /etc/hosts (for local subdomain routing):
#    127.0.0.1 {subdomain}.harelay.test

# 5. Visit https://{subdomain}.harelay.test:8000 and log in
```

### Testing with Valet

```bash
# Link with custom domain
valet link harelay --secure

# Update .env
APP_PROXY_DOMAIN=harelay.test
APP_PROXY_PORT=
APP_PROXY_SECURE=true
APP_URL=https://harelay.test
SESSION_DOMAIN=.harelay.test
```

Valet automatically handles wildcard subdomains: `https://{subdomain}.harelay.test`

### HA Add-on

The Home Assistant add-on is in a separate repository at `../harelay-addon/`. It's a Python WebSocket client that:
- Connects to the tunnel server on port 8081
- Authenticates with subdomain and token
- Proxies HTTP requests to local Home Assistant
- Proxies WebSocket connections for real-time features

## Environment Variables

Key variables to configure:

```env
# Application
APP_URL=https://harelay.com
APP_PROXY_DOMAIN=harelay.com          # Domain for subdomains
APP_PROXY_PORT=                       # Empty for production, 8000 for dev
APP_PROXY_SECURE=true                 # Use HTTPS for proxy URLs
SESSION_DOMAIN=.harelay.com           # Important for subdomain cookies

# Tunnel Server (Workerman)
TUNNEL_HOST=0.0.0.0                   # Bind address
TUNNEL_PORT=8081                      # Add-on connection port
WS_PROXY_PORT=8082                    # Browser WebSocket proxy port
WS_PROXY_PATH=/wss                    # Path-based WS for production (Nginx proxies to 8082)
TUNNEL_DEBUG=false                    # Enable verbose logging
```

### Production vs Development

| Aspect | Development | Production |
|--------|-------------|------------|
| APP_PROXY_PORT | 8000 | (empty) |
| APP_PROXY_SECURE | false | true |
| WS_PROXY_PATH | (empty) | /wss |
| Browser WS URL | ws://host:8082 | wss://host/wss |

## Security Notes

- Connection tokens are hashed (bcrypt) in database
- Tokens shown only once to user (on create/regenerate)
- Users must be authenticated to access their subdomain
- Owner verification on all proxy requests
- Subdomain sanitization: `preg_replace('/[^a-z0-9]/', '', $subdomain)`
- WebSocket path validation: only `/api/websocket` and `/api/hassio` allowed
- Stream IDs use cryptographically secure random bytes
- Security headers on proxy responses (X-Robots-Tag, X-Frame-Options)

## Working Guidelines

- **Always update documentation**: When adding features, commands, or changing behavior, update both `README.md` and `CLAUDE.md`
- **Run tests before committing**: Use `composer test` to verify changes
- **Format code**: Run `./vendor/bin/pint` before committing
