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
- **Cache**: Redis (for both general caching and tunnel IPC)
- **Session**: Database driver

### Database Tables

- `users` - User accounts (Breeze), includes `can_set_subdomain` flag for custom subdomain permission
- `ha_connections` - User's HA connection (subdomain, app_subdomain [optional], connection_token, status, last_connected_at, bytes_in, bytes_out)
- `subscriptions` - User subscription plans
- `device_codes` - Device pairing codes for add-on setup (expires after 15 minutes)
- `daily_traffic` - Daily traffic statistics per connection (ha_connection_id, date, bytes_in, bytes_out)

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Dashboard + subscription views
│   │   ├── ConnectionController.php   # Create/delete/regenerate token/update subdomain
│   │   ├── ProxyController.php        # HTTP proxying
│   │   ├── MarketingController.php    # Public pages
│   │   ├── DeviceLinkController.php   # Device code pairing (/link)
│   │   └── ProfileController.php      # User profile (Breeze)
│   ├── Controllers/Api/
│   │   └── DeviceCodeController.php   # Device code API endpoints
│   └── Middleware/
│       ├── SubdomainProxy.php         # Subdomain detection and proxy routing
│       ├── ProxySecurityHeaders.php   # Security headers for proxied responses
│       └── CheckSubscription.php
├── Models/
│   ├── User.php
│   ├── HaConnection.php               # Has getProxyUrl(), traffic formatting helpers
│   ├── Subscription.php
│   └── DeviceCode.php                 # Device pairing codes
├── Services/
│   └── TunnelManager.php              # Redis-cache IPC with tunnel server
└── Console/Commands/
    └── CreateTestConnection.php       # Creates test user/connection

tunnel-server.php                      # Workerman WebSocket tunnel server

routes/
├── web.php                            # Web routes + subdomain proxy + /link
├── api.php                            # Device code API + connection status endpoints
└── auth.php                           # Auth routes (Breeze)

resources/views/
├── dashboard/                         # User dashboard views
├── marketing/                         # Public marketing pages
├── device/                            # Device pairing views (link.blade.php)
├── errors/                            # Tunnel error pages (auth-required, disconnected, timeout)
└── components/                        # Blade components
```

## Tunnel System

### Architecture

The tunnel uses a Workerman-based WebSocket server that runs alongside Laravel:

- **Port 8081**: Add-on connections (authentication, HTTP request/response relay)
- **Port 8082**: Browser WebSocket proxy (for Home Assistant real-time features)

Communication between Laravel web requests and the tunnel server uses Redis cache (`Cache::store('redis')`) for fast, reliable IPC.

### Redis Configuration (CRITICAL)

**Laravel has multiple Redis connections with different prefixes:**

| Connection | Database | Prefix | Used By |
|------------|----------|--------|---------|
| `default` | DB 0 | `harelay-database-` | `Redis::` facade, pub/sub |
| `cache` | DB 1 | `harelay-cache-` | `Cache::store('redis')` |

**IMPORTANT for tunnel IPC:**
- **Always use `Cache::store('redis')`** for request/response IPC between TunnelManager and tunnel-server
- **Never use `Redis::` facade** in tunnel-server for IPC - it uses a different database and prefix
- **Pub/sub channels** use the `default` connection prefix, so subscribe to `{prefix}channel_name`

The tunnel-server gets the prefix via `config('database.redis.options.prefix')` for pub/sub subscriptions.

### Request Flow (HTTP)

1. User visits `subdomain.harelay.com/path`
2. `SubdomainProxy` middleware detects subdomain, calls `ProxyController`
3. `ProxyController` verifies auth and ownership
4. `TunnelManager::proxyRequest()` stores request in Redis
5. `tunnel-server.php` polls Redis, sends request to add-on via WebSocket
6. Add-on forwards to Home Assistant, returns response via WebSocket
7. `tunnel-server.php` stores response in Redis
8. `TunnelManager` retrieves response and returns to user

### Request Flow (WebSocket)

WebSocket connections are handled transparently via session cookie authentication:

1. Browser's native WebSocket connects to `/api/websocket` on the subdomain
2. Nginx routes the WebSocket upgrade request to `tunnel-server.php` (port 8082)
3. `onWebSocketConnect` callback extracts subdomain from Host header
4. Session cookie is decrypted and validated against the sessions table
5. Ownership verified: session user must own the subdomain
6. `onWebSocketConnected` callback sets up the stream and tells add-on to open WebSocket to HA
7. Messages are relayed bidirectionally through the tunnel

This transparent approach requires no JavaScript injection - Home Assistant's native WebSocket calls work directly.

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

The Home Assistant add-on is in a separate repository at `../harelay-addon/` (current version: 1.2.3). It's a Python WebSocket client that:
- Connects to the tunnel server on port 8081
- Authenticates with subdomain and token
- Proxies HTTP requests to local Home Assistant
- Proxies WebSocket connections for real-time features
- Supports device code pairing (leave credentials empty to start pairing mode)
- Stores credentials in `/data/credentials.json` (hidden from HA config UI)
- Has retry logic with exponential backoff for rate-limited API calls (429 errors)

### Device Code Pairing Flow

Users can pair the add-on without manually copying credentials:

1. User installs add-on, leaves subdomain/token empty
2. Add-on starts in pairing mode, calls `POST /api/device/code` to get a device code
3. Add-on displays: `Visit harelay.com/link and enter code: XXXX-XXXX`
4. User visits `/link`, logs in (or registers), enters the code
5. Server creates/links connection and stores plain token temporarily
6. Add-on polls `GET /api/device/poll/{deviceCode}` and receives credentials
7. Add-on saves credentials to `/data/credentials.json` and connects

**API Endpoints:**
```
POST /api/device/code          # Generate device code (returns device_code, user_code)
GET  /api/device/poll/{code}   # Poll for pairing status (returns credentials when linked)
GET  /api/connection/status    # Check connection status (for dashboard auto-refresh)
GET  /link                     # Web UI for entering pairing code
POST /link                     # Link device to user account (requires auth)
```

## Environment Variables

Key variables to configure:

```env
# Application
APP_URL=https://harelay.com
APP_PROXY_DOMAIN=harelay.com          # Domain for subdomains
APP_PROXY_PORT=                       # Empty for production, 8000 for dev
APP_PROXY_SECURE=true                 # Use HTTPS for proxy URLs
SESSION_DOMAIN=.harelay.com           # Important for subdomain cookies

# Redis (required for tunnel IPC)
REDIS_HOST=127.0.0.1                  # Redis server host
REDIS_PORT=6379                       # Redis server port

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

### Nginx WebSocket Routes (Production)

Three WebSocket paths are proxied to the tunnel server (see `DEPLOYMENT.md` for full config):

- `/tunnel` → port 8081 (add-on connections)
- `/api/websocket` → port 8082 (transparent browser WebSocket proxy, **requires Cookie header**)
- `/wss` → port 8082 (legacy path, also requires Cookie header)

**CRITICAL:** The `/api/websocket` location MUST include `proxy_set_header Cookie $http_cookie;` for session authentication to work.

## Security Notes

- Connection tokens are hashed (bcrypt) in database
- Tokens shown only once to user (on create/regenerate)
- Users must be authenticated to access their subdomain
- Owner verification on all proxy requests
- Subdomain sanitization: `preg_replace('/[^a-z0-9]/', '', $subdomain)`
- WebSocket path validation: only `/api/websocket` path allowed for transparent proxy
- Stream IDs use cryptographically secure random bytes
- Security headers on proxy responses (X-Robots-Tag, X-Frame-Options)
- Device codes expire after 15 minutes
- Plain tokens stored temporarily in device_codes, cleared after first poll

### Subdomain Types

| Type | Length | Auth | Use Case |
|------|--------|------|----------|
| Auto-generated | 8 chars | Login required | Default for new users |
| Custom | 2-32 chars | Login required | Users with `can_set_subdomain` flag |
| App subdomain | 32 chars | None (URL is auth) | Mobile app access |

**Note:** The tunnel-server WebSocket proxy validates subdomain length as 2-32 characters to support custom subdomains.

### Mobile App Access (App Subdomain)

Each connection can optionally have an `app_subdomain` for mobile app access without login:

- **Regular subdomain**: Requires HARelay login (safe to share)
- **App subdomain**: 32-character random string, no login required (URL is the auth)

**Implementation details:**
- `findConnectionBySubdomain()` in tunnel-server checks both `subdomain` and `app_subdomain` columns
- `ProxyController` and tunnel-server WebSocket proxy skip auth for app_subdomain requests
- Users generate/revoke app_subdomain from Settings page
- Security: 36^32 ≈ 6.3 × 10^49 combinations makes brute-force impossible

## Data Transfer Tracking

The tunnel server tracks bytes transferred per connection:
- **bytes_in**: Data uploaded by user (HTTP request bodies, browser→HA WebSocket messages)
- **bytes_out**: Data downloaded by user (HTTP response bodies, HA→browser WebSocket messages)

Traffic is buffered in memory and flushed to the database every 30 seconds for efficiency. Buffer is also flushed on graceful shutdown.

### Storage

Traffic data is stored in two places:
- **`ha_connections`**: Cumulative totals (`bytes_in`, `bytes_out`) for quick access
- **`daily_traffic`**: Daily breakdown for historical statistics (uses atomic UPSERT)

### Helper Methods

`HaConnection` model:
- `getFormattedBytesIn()` / `getFormattedBytesOut()` / `getFormattedTotalBytes()` - Human-readable format (KB/MB/GB)
- `formatBytes(int $bytes)` - Static helper for formatting

### Querying Daily Statistics

```php
// Get daily stats for a connection
DailyTraffic::where('ha_connection_id', $id)
    ->whereBetween('date', [$start, $end])
    ->get();

// Get total for a connection (alternative to cumulative columns)
DailyTraffic::where('ha_connection_id', $id)->sum('bytes_in');

// Get all-time stats grouped by day
DailyTraffic::where('ha_connection_id', $id)
    ->selectRaw('date, bytes_in, bytes_out')
    ->orderBy('date')
    ->get();
```

## User Permissions

- `can_set_subdomain` (users table): Allows user to set a custom subdomain instead of the auto-generated 8-character one. Set manually in database for specific users.

## Dashboard Features

- **Auto-refresh**: Dashboard polls `/api/connection/status` every 3 seconds when disconnected, auto-refreshes when connected
- **Loading animation**: Shows spinner with "Waiting for Connection" state while add-on connects
- **Device link code auto-formatting**: Automatically adds hyphen after 4th character (XXXX-XXXX format)
- **Data transfer stats**: Settings page shows downloaded/uploaded/total bytes in human-readable format

## Debugging

### Verify Redis is Working

```bash
# Test Redis server
redis-cli ping  # Should return PONG

# Test Laravel Redis connection
php artisan tinker
>>> Cache::store('redis')->put('test', 'ok', 60);
>>> Cache::store('redis')->get('test');  // Should return "ok"

# Watch Redis activity in real-time
redis-cli monitor
```

### Tunnel Server Logs

Enable verbose logging with `TUNNEL_DEBUG=true` in `.env`, then check stdout or:
```bash
# If running via systemd
sudo journalctl -u harelay-tunnel -f
```

## Common Pitfalls (READ BEFORE MAKING CHANGES)

### Redis in Workerman
- **Never use `Redis::` facade for IPC** - it uses different DB/prefix than `Cache::store('redis')`
- **Pub/sub channels need the prefix** - Laravel's `Redis::publish()` adds `harelay-database-` prefix, so Workerman's RedisClient must subscribe to the prefixed channel name
- **Don't use BLPOP/blocking operations** - Laravel's Redis facade doesn't handle these reliably in Workerman's long-running process

### Subdomain Validation
- **Custom subdomains can be 2+ characters** - don't hardcode 8-character minimum in regex patterns
- **App subdomains are 32 characters** - these are looked up via `app_subdomain` column, not `subdomain`
- **Always use `findConnectionBySubdomain()`** - it checks both regular and app subdomains

### WebSocket Proxy
- **Cookie header is required for session auth** - nginx must forward `Cookie $http_cookie` to port 8082
- **Only `/api/websocket` path is allowed** - this is validated in tunnel-server to prevent abuse
- **`tunnelSubdomain` vs `subdomain`** - when user accesses via app_subdomain, `tunnelSubdomain` is the regular subdomain used for add-on communication

### Session Authentication
- **SESSION_DOMAIN must include dot prefix** - e.g., `.harelay.com` for cookies to work across subdomains
- **Session cookie name is configurable** - defaults to `{app_name}-session`, read via `config('session.cookie')`

## Performance & Caching

### Known Issue: aiohttp Connection Pooling Corruption

The add-on uses aiohttp for HTTP requests to Home Assistant. **Connection pooling causes response corruption** (NS_ERROR_CORRUPTED_CONTENT, wrong MIME types) under high concurrency.

**Root cause:** When many concurrent requests share pooled connections, responses can get mixed up - a JS file request receives an HTML response, etc.

**Current solution:** Per-request sessions with shared connector:
```python
async with aiohttp.ClientSession(connector=self.http_connector, connector_owner=False) as session:
    async with session.request(...) as resp:
        ...
```

This isolates session state per request while still sharing the connection pool. If corruption persists, fall back to `force_close=True` on the connector (disables connection reuse entirely).

**Do NOT try:**
- Shared session across all requests (causes corruption)
- Semaphore-based concurrency limiting (doesn't fix the issue, just slows things down)
- Content-Length validation with compression enabled (aiohttp auto-decompresses, sizes won't match)

### Two-Level Static File Caching

Static files (`/frontend_latest/`, `/static/`, `/hacsfiles/`) are cached at two levels:

| Layer | Location | TTL | Benefit |
|-------|----------|-----|---------|
| **Laravel/Redis** | `TunnelManager.php` | 24 hours | Skips entire tunnel round-trip |
| **Add-on memory** | `run.py` | Session lifetime | Skips HA request |

**Cache keys:**
- Laravel: `tunnel:static:{subdomain}:{uri}`
- Add-on: In-memory dict keyed by URI

**Flow:**
1. Request comes to Laravel
2. Check Redis cache → HIT = return immediately (no tunnel)
3. Cache MISS → send through tunnel
4. Add-on checks memory cache → HIT = return (no HA request)
5. Cache MISS → request from HA
6. Response cached at both levels for future requests

**Cache invalidation:** Hashed filenames (e.g., `app.64d32f86.js`) mean content changes = new filename. No manual invalidation needed.

### Browser Caching

`ProxyController` adds aggressive caching headers for static assets:
```php
Cache-Control: public, max-age=31536000, immutable  // 1 year
```

This means after first load, browsers cache static files locally.

### Local Add-on Development

To test add-on changes without pushing to GitHub:

1. Copy add-on to HA's local add-ons folder:
```bash
scp -r /path/to/harelay-addon/harelay root@<HA_IP>:/addons/harelay_local/
```

2. In HA: Settings → Add-ons → Add-on Store → ⋮ → Check for updates

3. Install the local add-on

4. For iterating:
```bash
scp /path/to/run.py root@<HA_IP>:/addons/harelay_local/
ha addons restart local_harelay_local
ha addons logs local_harelay_local -f
```

**Tip:** Add a version tag to startup log for verification:
```python
logger.info('HARelay Add-on starting... (v8-cache)')
```

## Working Guidelines

- **Always update documentation**: When adding features, commands, or changing behavior, update both `README.md` and `CLAUDE.md`
- **Run tests before committing**: Use `composer test` to verify changes
- **Format code**: Run `./vendor/bin/pint` before committing
- **Check nginx config**: When debugging WebSocket issues, verify nginx forwards required headers
- **Test both subdomain types**: When changing proxy/WebSocket code, test both regular and app subdomains
