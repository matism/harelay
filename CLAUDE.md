# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**HARelay** - A Laravel 12 application providing secure remote access to Home Assistant. Users get unique subdomains (e.g., `abc123.harelay.com`) and install a lightweight HA add-on that establishes a WebSocket tunnel for proxying requests.

### Key Components

- **Marketing Site**: Landing page, pricing, how-it-works, comparison pages
- **User Dashboard**: Connection management, setup guide, settings, 2FA
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
- **Authentication**: Laravel Breeze (Blade) with optional 2FA
- **Tunnel Server**: Workerman WebSocket (ports 8081 + 8082)
- **Tunnel Protocol**: MessagePack binary (no JSON, no base64)
- **Database**: MySQL (production), SQLite (development/testing)
- **Queue**: Database driver
- **Cache**: Redis with igbinary + LZ4 compression
- **Session**: Database driver

### Database Tables

- `users` - User accounts (Breeze), includes `can_set_subdomain` flag for custom subdomain permission, 2FA columns
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
│   ├── HaConnection.php               # Has getProxyUrl(), findBySubdomain(), traffic formatting
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
| `cache` | DB 1 | `harelay:` | `Cache::store('redis')` |

**Redis Cache Optimization:**
The cache connection uses igbinary serialization + LZ4 compression for efficient storage:
```php
'cache' => [
    'prefix' => '',  // Empty - prefix set in cache.php as 'harelay:'
    'serializer' => Redis::SERIALIZER_IGBINARY,
    'compression' => Redis::COMPRESSION_LZ4,
    'compression_level' => 3,
],
```

**IMPORTANT for tunnel IPC:**
- **Always use `Cache::store('redis')`** for request/response IPC between TunnelManager and tunnel-server
- **Never use `Redis::` facade** in tunnel-server for IPC - it uses a different database and prefix
- **Pub/sub channels** use the `default` connection prefix, so subscribe to `{prefix}channel_name`
- **Response bodies stored as raw bytes** - no base64 encoding (igbinary handles binary data efficiently)

The tunnel-server gets the prefix via `config('database.redis.options.prefix')` for pub/sub subscriptions.

### Request Flow (HTTP)

1. User visits `subdomain.harelay.com/path`
2. `SubdomainProxy` middleware detects subdomain, calls `ProxyController`
3. `ProxyController` verifies auth and ownership
4. `TunnelManager::proxyRequest()` stores request in Redis
5. `tunnel-server.php` polls Redis (2ms interval), sends request to add-on via WebSocket
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

### Add-on Protocol (MessagePack Binary)

The HA add-on (`../harelay-app/`) connects via WebSocket to port 8081 using **MessagePack binary protocol** (not JSON).

**Why MessagePack:**
- **40-60% bandwidth reduction** vs JSON+base64
- **10-20% latency improvement** (faster encode/decode)
- **Raw binary bodies** - no base64 encoding overhead

**Requirements:**
- PHP: `php-msgpack` extension
- Python: `msgpack` package (py3-msgpack in Alpine)

**Wire Format:**
- WebSocket frame type: Binary (`\x82`)
- Encoding: MessagePack with `use_bin_type=True`
- Body data: Raw bytes (no base64)

**Authentication (MessagePack):**
```python
# Add-on sends:
{"type": "auth", "subdomain": "abc123", "token": "secret"}
# Server responds:
{"type": "auth_result", "success": True, "subdomain": "abc123"}
```

**HTTP Request/Response (MessagePack):**
```python
# Server sends request:
{"type": "request", "request_id": "uuid", "method": "GET", "uri": "/", "headers": {}, "body": None}
# Add-on sends response (body is raw bytes, not base64):
{"type": "response", "request_id": "uuid", "status_code": 200, "headers": {}, "body": b"<html>..."}
```

**WebSocket Proxy (MessagePack):**
```python
# Server opens WebSocket stream:
{"type": "ws_open", "stream_id": "id", "path": "/api/websocket"}
{"type": "ws_open", "stream_id": "id", "path": "/api/hassio_ingress/.../ws", "ingress_session": "..."}

# Bidirectional messages:
{"type": "ws_message", "stream_id": "id", "message": "..."}

# Close stream:
{"type": "ws_closed", "stream_id": "id"}  # From add-on
{"type": "ws_close", "stream_id": "id"}   # From server
```

Note: `ingress_session` is only included for ingress WebSocket paths. The add-on uses this to authenticate with HA's ingress system.

**Heartbeat/Keepalive (MessagePack):**
```python
# Add-on sends:
{"type": "heartbeat"}
# Server responds:
{"type": "pong"}

# Server can also initiate:
{"type": "ping"}
# Add-on responds:
{"type": "pong"}
```

**Subdomain Changed (after user changes subdomain):**
```python
{"type": "subdomain_changed", "old_subdomain": "abc123", "new_subdomain": "myha"}
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
# See ../harelay-app/ for add-on setup

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

The Home Assistant add-on is in a separate repository at `../harelay-app/` (current version: 1.4.2). It's a Python asyncio WebSocket client that:
- Connects to the tunnel server on port 8081
- Authenticates with subdomain and token
- Proxies HTTP requests to local Home Assistant
- Proxies WebSocket connections for real-time features (main HA + ingress add-ons)
- Supports device code pairing (leave credentials empty to start pairing mode)
- Stores credentials in `/data/credentials.json` (hidden from HA config UI)
- Has retry logic with exponential backoff for rate-limited API calls (429 errors)
- Uses LRU cache (100MB max) for static file responses
- Includes health check with 60-second timeout for detecting stale connections

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
- WebSocket path validation: only `/api/websocket` and `/api/hassio_ingress/{token}/ws` allowed
- Stream IDs use cryptographically secure random bytes (32 hex chars)
- Security headers on proxy responses (X-Robots-Tag, X-Frame-Options) - relaxed for app_subdomain
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
- **App subdomain**: 32-character random string, no HARelay login required (URL is the auth)

**Implementation details:**
- `findConnectionBySubdomain()` in tunnel-server checks both `subdomain` and `app_subdomain` columns
- `ProxyController` and tunnel-server WebSocket proxy skip HARelay auth for app_subdomain requests
- Users generate/revoke app_subdomain from Settings page
- Security: 36^32 ≈ 6.3 × 10^49 combinations makes brute-force impossible

**IMPORTANT - App Subdomain vs Regular Subdomain Differences:**

| Aspect | Regular Subdomain | App Subdomain |
|--------|-------------------|---------------|
| HARelay auth | Required (verifies ownership) | Not required (URL is auth) |
| HA auth | Required (after HARelay login) | Required (directly) |
| Use case | Web browser with HARelay account | Mobile apps, direct HA access |

**Cookie Handling (same for both subdomain types):**

All cookies pass through in both directions:
- **Request cookies**: All browser cookies forwarded to HA
- **Response cookies**: All `Set-Cookie` headers from HA passed through with:
  - `Domain` attribute stripped (browser uses request origin)
  - `Secure` flag added if proxy uses HTTPS
  - `SameSite=Lax` added if not present

Note: HA's main authentication uses tokens stored in localStorage, not cookies. The `ingress_session` cookie is used by HA add-ons for ingress functionality.

**HA Login Persistence on App Subdomain:**

When logging into HA via app_subdomain, users must check **"Angemeldet bleiben" / "Stay logged in"** for the auth token to persist in localStorage. Without this checkbox:
- Token is kept in memory only
- Works during the session
- Lost on page refresh (user must log in again)

This is HA's intended behavior, not a HARelay bug.

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

# Check PHP extensions
php -m | grep -E 'redis|igbinary|msgpack'

# Verify LZ4 compression support
php -r "echo defined('Redis::COMPRESSION_LZ4') ? 'LZ4: OK' : 'LZ4: MISSING';"

# Test Laravel Redis connection with compression
php artisan tinker
>>> Cache::store('redis')->put('test', str_repeat('x', 10000), 60);
>>> Cache::store('redis')->get('test') === str_repeat('x', 10000);  // Should be true

# Check cache keys (note: harelay: prefix)
redis-cli -n 1 keys 'harelay:*'

# Watch Redis activity in real-time
redis-cli monitor
```

**Note:** When viewing Redis values directly, you'll see binary data (igbinary serialization). This is expected - Laravel handles serialization/deserialization automatically.

### Tunnel Server Logs

Enable verbose logging with `TUNNEL_DEBUG=true` in `.env`, then check stdout or:
```bash
# If running via systemd
sudo journalctl -u harelay-tunnel -f
```

## Common Pitfalls (READ BEFORE MAKING CHANGES)

### Connection Replacement in Workerman
- **`onClose` fires asynchronously** - Workerman's `close()` may defer `onClose` until the send buffer drains. Never store new state before `close()` completes, or guard `onClose` against acting on replaced connections.
- **Always check identity in `onClose`** - Use `$addonConnections[$subdomain] === $conn` to ensure the closing connection is still the active one. A new connection may have already replaced it.
- **Clean up before `close()`** - When replacing a connection, remove the old entry from shared state maps (`$addonConnections`, `$browserWsConnections`, `$addonWsStreams`) *before* calling `$oldConn->close()`.

### Redis in Workerman
- **Pending requests use Redis Hash** - `TunnelManager` uses atomic HSET/HDEL/HGETALL operations to prevent race conditions when multiple requests arrive simultaneously
- **Hash key format** - `harelay:tunnel:pending:{subdomain}` with each request as a field (request ID → serialized data)
- **Pub/sub channels need the prefix** - Laravel's `Redis::publish()` adds `harelay-database-` prefix, so Workerman's RedisClient must subscribe to the prefixed channel name
- **Don't use BLPOP/blocking operations** - Laravel's Redis facade doesn't handle these reliably in Workerman's long-running process
- **Response storage still uses Cache** - Only pending requests use Redis Hash; responses use `Cache::store('redis')` with igbinary serialization

### Subdomain Validation
- **Custom subdomains can be 2+ characters** - don't hardcode 8-character minimum in regex patterns
- **App subdomains are 32 characters** - these are looked up via `app_subdomain` column, not `subdomain`
- **Always use `findConnectionBySubdomain()`** - it checks both regular and app subdomains

### WebSocket Proxy
- **Cookie header is required for session auth** - nginx must forward `Cookie $http_cookie` to port 8082
- **Only `/api/websocket` path is allowed** for main HA WebSocket - validated in tunnel-server
- **Ingress paths** (`/api/hassio_ingress/{token}/ws`) are also supported for HA add-on WebSockets
- **`tunnelSubdomain` vs `subdomain`** - when user accesses via app_subdomain, `tunnelSubdomain` is the regular subdomain used for add-on communication
- **Ingress WebSocket** requires `ingress_session` cookie - passed from browser through tunnel to add-on

### Ingress WebSocket Support

HA add-ons (like code-server, Terminal) use ingress WebSockets at `/api/hassio_ingress/{token}/ws`. These require special handling:

1. **tunnel-server.php** detects ingress paths via regex: `^/api/hassio_ingress/[^/]+/ws$`
2. **ingress_session cookie** is extracted and passed to add-on in `ws_open` message
3. **Add-on** creates WebSocket to HA with the cookie in headers:
   ```python
   ws_headers = [('Cookie', f'ingress_session={ingress_session}')]
   ha_ws = await websockets.connect(url, additional_headers=ws_headers, ...)
   ```
4. **PermissiveDeflateFactory** handles HA's permessage-deflate negotiation quirks

### Session Authentication
- **SESSION_DOMAIN must include dot prefix** - e.g., `.harelay.com` for cookies to work across subdomains
- **Session cookie name is configurable** - defaults to `{app_name}-session`, read via `config('session.cookie')`
- **Don't set cookie back on subdomain** - only read session, don't write. Writing causes logout on main domain.

### IP Address Consistency
- **HA rejects auth if IP changes during login_flow**
- Always set `X-Forwarded-For` and `X-Real-IP` headers with client IP
- This ensures consistent IP throughout the auth flow

## Performance & Caching

### aiohttp Configuration (CRITICAL)

The add-on uses aiohttp for HTTP requests to Home Assistant. Two critical settings:

**1. `auto_decompress=False` (REQUIRED)**
```python
async with aiohttp.ClientSession(connector=self.http_connector, connector_owner=False, auto_decompress=False) as session:
```
Without this, aiohttp decompresses gzip responses but keeps the `Content-Encoding: gzip` header, causing browsers to try to decompress already-decompressed data (garbled JS files).

**2. Per-request sessions with shared connector**
```python
async with aiohttp.ClientSession(connector=self.http_connector, connector_owner=False, auto_decompress=False) as session:
    async with session.request(...) as resp:
        ...
```
This isolates session state per request while still sharing the connection pool. Prevents response corruption under high concurrency.

**Do NOT try:**
- Shared session across all requests (causes corruption)
- Semaphore-based concurrency limiting (doesn't fix the issue, just slows things down)
- Content-Length validation with compression enabled (aiohttp auto-decompresses, sizes won't match)

### Add-on Stability Improvements (v1.4.2)

The add-on has several stability improvements to handle concurrent WebSocket streams and proper cleanup:

**WebSocket Stream Locking:**
```python
# Each stream has its own lock to prevent race conditions
async def _get_stream_lock(self, stream_id: str) -> asyncio.Lock:
    async with self._locks_lock:  # Lock for creating locks
        if stream_id not in self._ws_locks:
            self._ws_locks[stream_id] = asyncio.Lock()
        return self._ws_locks[stream_id]
```

**Task Management:**
- All async tasks tracked in `_active_tasks` set
- Proper cancellation handling with `asyncio.CancelledError`
- Cleanup of locks, streams, and pending messages on shutdown
- Graceful shutdown via `_shutdown_event`

**Health Checks:**
- `HEALTH_CHECK_TIMEOUT = 60` seconds - forces reconnect if server stops responding
- Heartbeat loop sends keepalive every 30 seconds
- Server-side keepalive check closes stale connections after 60 seconds

**Multiple Response Headers:**

When HA sends multiple headers with the same name (e.g., multiple `Set-Cookie`), aiohttp's `dict(resp.headers)` only keeps the last value. Fixed by:
```python
response_headers = {}
for key, value in resp.headers.items():
    if key in response_headers:
        if isinstance(response_headers[key], list):
            response_headers[key].append(value)
        else:
            response_headers[key] = [response_headers[key], value]
    else:
        response_headers[key] = value
```

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

### Request Cancellation

When a client disconnects mid-request:
1. `TunnelManager` registers shutdown function
2. Marks request as cancelled in Redis (`tunnel:cancelled:{requestId}`)
3. Removes from pending queue
4. Tunnel server checks cancelled flag before processing
5. Discards response if request was cancelled

This prevents wasted processing and stale responses.

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

## Debugging Tips

### App Subdomain Auth Issues

If users report auth not persisting on app_subdomain:

1. **Check "Stay logged in" checkbox** - HA only saves tokens to localStorage if this is checked
2. **Check browser console** for JavaScript errors
3. **Check Network tab** - verify `/auth/token` returns valid `access_token`
4. **Check Application → Local Storage** - look for `hassTokens` key

If `ha-version` is saved but `hassTokens` is not, the user simply didn't check "Stay logged in".

### Cookie Issues

If cookies aren't working:

1. **Check Set-Cookie headers** in Network tab - are they being set?
2. **Check cookie Domain attribute** - should match request origin or be absent
3. **Check Secure flag** - must match HTTPS setting
4. **Check SameSite** - should be `Lax` for cross-origin compatibility

For app_subdomain, cookies should pass through with Domain stripped. For regular subdomain, only `ingress_session` is handled.

### WebSocket Connection Issues

1. **Check nginx config** - must forward `Cookie` header to port 8082
2. **Check tunnel-server logs** - enable `TUNNEL_DEBUG=true`
3. **Check add-on logs** - `ha addons logs local_harelay`
4. **Verify path** - main HA uses `/api/websocket`, ingress uses `/api/hassio_ingress/{token}/ws`

### Add-on Stability Issues

If add-on crashes or connections drop:

1. **Check for Python errors** in add-on logs
2. **Verify asyncio task cleanup** - tasks should be cancelled on shutdown
3. **Check health timeout** - connection considered stale after 60s without response
4. **Memory usage** - static cache limited to 100MB

### Subdomain Change Issues

If subdomain change doesn't propagate:

1. **Check Redis pub/sub** - `redis-cli monitor` should show `tunnel:subdomain_changes`
2. **Check tunnel-server logs** - should show "Subdomain change:" message
3. **Verify add-on receives notification** - should reconnect with new subdomain

## Development History & Lessons Learned

### What We Tried That Didn't Work

#### Redis BLPOP for Request Handling
- **Tried:** Using BLPOP for blocking reads instead of polling
- **Problem:** Doesn't work reliably in Workerman's long-running process
- **Solution:** Reverted to polling with fast interval (2ms)

#### Header Stripping / Service Worker
- **Tried:** More aggressive header stripping and service worker caching
- **Problem:** Caused issues with HA's frontend
- **Solution:** Reverted, increased TTL instead

#### Auto-auth with Supervisor Token
- **Tried:** Automatically authenticating app_subdomain WebSocket with supervisor token
- **Problem:** Would bypass HA login entirely (security issue)
- **Solution:** User must log in to HA directly on app_subdomain

#### Shared aiohttp Session
- **Tried:** Single aiohttp session for all requests (connection pooling)
- **Problem:** Response corruption under high concurrency
- **Solution:** Per-request sessions with shared connector

#### JSON + Base64 Protocol
- **Tried:** Using JSON encoding with base64 for binary bodies
- **Problem:** 33% overhead from base64, plus JSON encoding overhead
- **Solution:** Switched to MessagePack binary protocol with raw bytes

### Issues That Were Fixed

#### IP Address Changed During Auth
- **Issue:** HA rejects auth if IP changes during login_flow
- **Fix:** Always set X-Forwarded-For with client IP

#### Session Logout on Main Domain
- **Issue:** Writing session cookie on subdomain caused logout on main domain
- **Fix:** Only read session, never write cookie back on subdomain

#### Custom Subdomain Validation
- **Issue:** Regex assumed 8-character subdomains
- **Fix:** Allow 2-32 characters for custom subdomains

#### Ingress WebSocket Authentication
- **Issue:** Ingress add-ons (code-server, Terminal) WebSockets failing
- **Fix:** Extract and pass `ingress_session` cookie through tunnel

#### Wrong Subdomain Caching
- **Issue:** Static files cached without subdomain in key
- **Fix:** Include subdomain in cache key: `tunnel:static:{subdomain}:{uri}`

#### Client Disconnect Handling
- **Issue:** Requests continued processing after client disconnected
- **Fix:** Track cancelled requests, clean up properly

#### MessagePack Binary Protocol Migration
- **Issue:** JSON + base64 encoding caused ~40% bandwidth overhead
- **Fix:** Migrated to MessagePack binary protocol with raw bytes:
  - PHP: Uses `msgpack_pack()`/`msgpack_unpack()` functions
  - Python: Uses `msgpack.packb()`/`msgpack.unpackb()` with `use_bin_type=True`
  - WebSocket frames: Binary type (`\x82`)
  - Response bodies: Raw bytes stored in Redis (no base64)
  - Redis: igbinary serialization + LZ4 compression

#### Redis Key Prefix Duplication
- **Issue:** Cache keys had double prefix (`harelay-database-harelay-cache-`)
- **Fix:** Set empty prefix at Redis connection level, single prefix `harelay:` in cache.php

#### Concurrent Requests Race Condition (JS Module Loading Failures)
- **Issue:** When browser loaded HA frontend, many parallel JS requests were made. The pending request storage used non-atomic get-modify-put pattern:
  ```php
  $pending = Cache::get($key, []);  // Request A and B both get {}
  $pending[$id] = $request;          // A adds itself, B adds itself
  Cache::put($key, $pending);        // B overwrites A - request A is lost!
  ```
  This caused intermittent "Loading failed for the module" errors because requests were silently dropped.
- **Fix:** Changed to atomic Redis Hash operations:
  - `HSET` to add requests (atomic, no overwrite)
  - `HDEL` to remove processed requests (atomic)
  - `HGETALL` to retrieve all pending requests
  - Hash key: `harelay:tunnel:pending:{subdomain}`
- **Related fix:** Also needed `auto_decompress=False` in add-on's aiohttp to preserve Content-Encoding header/body consistency

#### Content-Encoding Header Mismatch
- **Issue:** aiohttp auto-decompresses gzip responses by default, but keeps the `Content-Encoding: gzip` header. Browser received decompressed data but tried to decompress it again, causing garbled JS files.
- **Fix:**
  1. Add-on: Set `auto_decompress=False` on aiohttp ClientSession
  2. Server: Preserve `Content-Encoding` header (don't strip it in ProxyController)

#### Connection Replacement Race Condition (Tunnel Dies After 1-2 Days)
- **Symptom:** After 1-2 days, the tunnel connection drops. The add-on reconnects successfully (logs show "Connected"), but HA is inaccessible — WebSocket streams close after 1 message, HTTP requests hang. Only restarting the add-on fixes it.
- **Root cause:** A race condition in `tunnel-server.php` when the add-on reconnects while the server still holds the old (dead) connection.

  **How it happens:** When the network drops, the add-on detects it within ~30s (websockets library ping timeout) and reconnects immediately. But the server's stale detection takes up to 90s (30s timer + 60s threshold). So the add-on sends `auth` while the old connection is still registered. The auth handler called `$oldConn->close()` and then stored the new connection:
  ```php
  $addonConnections[$subdomain]->close();        // Triggers onClose LATER
  $addonConnections[$subdomain] = $newConn;      // Store new connection
  // ... then Workerman fires onClose for old connection:
  unset($addonConnections[$subdomain]);           // WIPES THE NEW CONNECTION!
  ```
  Workerman's `onClose` fires asynchronously (after the send buffer drains), so it ran *after* the new connection was stored — deleting `$addonConnections`, `$browserWsConnections`, and `$addonWsStreams` for the subdomain. The tunnel server lost all knowledge of the active connection.

  **Why restarting fixed it:** A full add-on restart takes several seconds. By then, the server's 90s stale timeout fires first, cleaning up the old connection properly. The add-on reconnects to a clean state — no race condition.

- **Fix:** Two changes in `tunnel-server.php`:
  1. **Auth handler:** Clean up old connection's state (browser WS connections, stream mappings) *before* calling `close()`, so cleanup doesn't depend on `onClose` timing.
  2. **`onClose` handler:** Guard with `$addonConnections[$subdomain] === $conn` — only clean up if the closing connection is still the active one. If it's been replaced, the auth handler already handled cleanup.

## Known Limitations

1. **HA token persistence requires user action** - "Stay logged in" must be checked on app_subdomain
2. **localStorage is per-origin** - tokens saved on regular subdomain aren't available on app_subdomain (different origins)
3. **Ingress session cookies** - must be passed through for HA add-on WebSockets to work
4. **Multiple Set-Cookie headers** - special handling needed (aiohttp `dict()` loses duplicates)
5. **Blocking Redis operations** - don't work in Workerman, must use polling

## Things We Tried That Didn't Fix the Issue

When debugging app_subdomain auth persistence (token not saved to localStorage):

1. **Cookie pass-through** - Enabled passing all cookies for app_subdomain. Necessary for auth flow but doesn't affect localStorage.
2. **Set-Cookie domain stripping** - Strips Domain attribute so browser uses request origin. Correct but doesn't affect localStorage.
3. **CSP header removal** - Removed restrictive CSP for app_subdomain. Doesn't affect localStorage.
4. **Cache-Control changes** - Let HA control cache headers on app_subdomain. Doesn't affect localStorage.
5. **Auto-auth with supervisor token** - Attempted to auto-authenticate WebSocket with supervisor token for app_subdomain. **WRONG APPROACH** - this would bypass HA login entirely, which is a security issue.

**The actual fix:** User needs to check "Stay logged in" / "Angemeldet bleiben" during HA login. This is HA's intended behavior - without it, tokens are memory-only.

The cookie/header changes ARE still necessary for the auth flow to work, but they don't affect whether the token is persisted to localStorage. That's purely controlled by HA's frontend based on the checkbox.

## SEO & Content Management

### Auto-Generated SEO Files

The following files are dynamically generated and always up-to-date:

| File | Route | Source |
|------|-------|--------|
| `/sitemap.xml` | `MarketingController::sitemap()` | `getPages()` array |
| `/llms.txt` | `MarketingController::llmsTxt()` | `getPages()` array |

### Adding a New Marketing Page

When adding a new marketing page, update these locations:

1. **Create the view**: `resources/views/marketing/new-page.blade.php`
2. **Create structured data**: `resources/views/components/structured-data/new-page.blade.php`
3. **Add route**: `routes/web.php`
4. **Add to `getPages()`**: `app/Http/Controllers/MarketingController.php`
   ```php
   [
       'route' => 'marketing.new-page',
       'priority' => '0.8',
       'changefreq' => 'monthly',
       'title' => 'Page Title',
       'description' => 'Page description for SEO.',
   ],
   ```
5. **Update robots.txt**: Add `Allow: /new-page` in `public/robots.txt`

### Structured Data

Each marketing page has its own structured data component in `resources/views/components/structured-data/`:

| Page | Schema Type | File |
|------|-------------|------|
| security | FAQPage | `structured-data/security.blade.php` |
| how-it-works | HowTo + FAQPage | `structured-data/how-it-works.blade.php` |
| vs-nabu-casa | WebPage + ItemList | `structured-data/vs-nabu-casa.blade.php` |
| vs-homeflow | WebPage + ItemList | `structured-data/vs-homeflow.blade.php` |
| privacy | WebPage | `structured-data/privacy.blade.php` |
| imprint | WebPage + Organization | `structured-data/imprint.blade.php` |

**Important**: Use `@@context` and `@@type` (double `@`) in JSON-LD to escape Blade directive parsing.

Pages include structured data via:
```blade
<x-slot name="structuredData"><x-structured-data.page-name /></x-slot>
```

### Updating Content

When updating FAQs or feature descriptions:

1. **Update the page content** in `resources/views/marketing/*.blade.php`
2. **Update structured data** in `resources/views/components/structured-data/*.blade.php` (keep FAQ questions in sync)
3. **Update `getPages()` description** if the page summary changed
4. **sitemap.xml and llms.txt** auto-update (no action needed)

### Files Overview

```
public/
└── robots.txt                              # Static, references sitemap.xml and llms.txt

app/Http/Controllers/
└── MarketingController.php                 # Contains getPages(), sitemap(), llmsTxt()

resources/views/
├── marketing/                              # Page templates
│   ├── home.blade.php
│   ├── how-it-works.blade.php
│   ├── security.blade.php
│   ├── privacy.blade.php
│   ├── imprint.blade.php
│   ├── vs-nabu-casa.blade.php
│   └── vs-homeflow.blade.php
└── components/
    ├── marketing-layout.blade.php          # Global structured data (SoftwareApplication, Organization)
    └── structured-data/                    # Page-specific structured data
        ├── security.blade.php
        ├── how-it-works.blade.php
        ├── privacy.blade.php
        ├── imprint.blade.php
        ├── vs-nabu-casa.blade.php
        └── vs-homeflow.blade.php
```

## Working Guidelines

- **Always update documentation**: When adding features, commands, or changing behavior, update both `README.md` and `CLAUDE.md`
- **Keep SEO in sync**: When adding/updating marketing pages, follow the SEO checklist above
- **Run tests before committing**: Use `composer test` to verify changes
- **Format code**: Run `./vendor/bin/pint` before committing
- **Check nginx config**: When debugging WebSocket issues, verify nginx forwards required headers
- **Test both subdomain types**: When changing proxy/WebSocket code, test both regular and app subdomains
- **Check add-on version**: The add-on at `../harelay-app/` should be kept in sync with server changes
- **Don't use blocking Redis**: Always use polling, not BLPOP/BRPOP
- **Preserve IP consistency**: Always forward X-Forwarded-For for HA auth flows
- **MessagePack binary protocol**: All tunnel communication uses MessagePack, not JSON. Bodies are raw bytes, not base64.
- **Verify PHP extensions**: Ensure `msgpack`, `igbinary`, and `redis` (with LZ4) are installed
