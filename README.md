# HARelay

Secure remote access proxy for Home Assistant. Access your smart home from anywhere without port forwarding.

## Overview

HARelay provides a secure tunnel between your Home Assistant instance and the internet. Users install a lightweight app (formerly add-on) on their Home Assistant, which establishes an outbound WebSocket connection to the HARelay server. This allows remote access without exposing any ports on your home network.

**Key Features:**
- No port forwarding required
- Works behind CGNAT and firewalls
- Unique subdomain per user (e.g., `yourname.harelay.com`)
- End-to-end encryption via TLS
- Session-based authentication
- Full WebSocket support for real-time Home Assistant features
- Mobile app access via long-form app subdomain (no login required)
- Device code pairing for easy app setup
- Two-factor authentication support
- Data transfer tracking

## Architecture

```
┌─────────────────┐     HTTPS/WSS     ┌──────────────────────────────────┐
│  User Browser   │ ◄───────────────► │         HARelay Server           │
│                 │                   │  - Laravel App (HTTP proxy)      │
└─────────────────┘                   │  - Workerman Tunnel Server       │
        │                             │  - WebSocket Proxy               │
        │                             │  - Redis (igbinary + LZ4)        │
        │                             └──────────────────────────────────┘
        │ visits subdomain.harelay.com           │
        │                                        │ WebSocket tunnel
        │                                        │ (MessagePack binary)
        │                             ┌──────────────────────────────────┐
        └────────────────────────────►│       Home Assistant App          │
                                      │  - Connects via WebSocket        │
                                      │  - Proxies HTTP requests         │
                                      │  - Proxies WebSocket streams     │
                                      └──────────────────────────────────┘
                                                 │
                                                 ▼
                                      ┌──────────────────────────────────┐
                                      │      Home Assistant Instance     │
                                      │      (localhost:8123)            │
                                      └──────────────────────────────────┘
```

### How It Works

1. **User Registration**: User creates an account on HARelay
2. **App Installation**: User installs the HARelay app on Home Assistant
3. **Device Pairing**: App displays a pairing code (XXXX-XXXX), user enters it at harelay.com/link to link their account
4. **Tunnel Establishment**: The app connects to HARelay via WebSocket (outbound connection, no ports needed)
5. **Remote Access**: User visits their subdomain, authenticates, and requests are proxied through the tunnel to Home Assistant

The dashboard auto-refreshes when the connection status changes, showing real-time connection state.

## Requirements

- PHP 8.2+ with extensions:
  - `php-redis` (with igbinary + LZ4 support)
  - `php-msgpack` (for binary protocol)
  - `php-igbinary` (for Redis serialization)
- Composer
- Node.js 20.19+ or 22.12+ (for Vite)
- Redis (required for tunnel IPC, uses LZ4 compression)
- MySQL/PostgreSQL (production) or SQLite (development)

## Quick Start

```bash
# Clone and install
git clone https://github.com/your-org/harelay.git
cd harelay
composer setup

# Start development server
composer dev
```

This runs:
- **server**: Laravel development server (port 8000)
- **queue**: Queue worker for background jobs
- **logs**: Laravel Pail for real-time log viewing
- **vite**: Vite dev server for hot reloading
- **tunnel**: Workerman tunnel server (ports 8081/8082)

## Development

### Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for local development:

```env
APP_NAME=HARelay
APP_URL=http://localhost:8000
APP_PROXY_DOMAIN=harelay.test
APP_PROXY_PORT=8000
APP_PROXY_SECURE=false

SESSION_DOMAIN=.harelay.test

DB_CONNECTION=sqlite

# Redis is required for tunnel communication
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Local Development with Valet

For seamless wildcard subdomain support:

```bash
cd /path/to/harelay
valet link harelay --secure
```

Update `.env`:

```env
APP_URL=https://harelay.test
APP_PROXY_DOMAIN=harelay.test
APP_PROXY_PORT=
APP_PROXY_SECURE=true
SESSION_DOMAIN=.harelay.test
```

Access subdomains: `https://{subdomain}.harelay.test`

### Running Tests

```bash
composer test
```

### Code Formatting

```bash
./vendor/bin/pint
```

## Subdomain Types

HARelay supports three subdomain types:

| Type | Length | Auth Required | Use Case |
|------|--------|---------------|----------|
| Auto-generated | 8 chars | Yes | Default for new users |
| Custom | 2-32 chars | Yes | Users with permission |
| App subdomain | 32 chars | No | Mobile app access |

**App Subdomain**: A 32-character random string that allows access without HARelay login. The URL itself is the authentication - users still need to log into Home Assistant directly. Generated from the Settings page.

## Production Deployment (Ubuntu/DigitalOcean)

See `DEPLOYMENT.md` for comprehensive deployment instructions.

### Quick Overview

1. **Server Setup**: Ubuntu 24.04 LTS, PHP 8.3, MySQL/PostgreSQL, Redis, Nginx
2. **SSL Certificates**: Let's Encrypt with wildcard certificate for subdomains
3. **Systemd Services**: Tunnel server and queue worker as background services
4. **Zero-Downtime Deploys**: Symlink-based deployment with instant rollback

### Key Nginx Configuration

Three WebSocket paths must be proxied:

```nginx
# App connections (port 8081)
location /tunnel {
    proxy_pass http://127.0.0.1:8081;
    # ... WebSocket headers
}

# Browser WebSocket - MUST include Cookie header
location /api/websocket {
    proxy_pass http://127.0.0.1:8082;
    proxy_set_header Cookie $http_cookie;  # CRITICAL
    # ... WebSocket headers
}

# Legacy WebSocket path
location /wss {
    proxy_pass http://127.0.0.1:8082;
    proxy_set_header Cookie $http_cookie;
    # ... WebSocket headers
}
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_PROXY_DOMAIN` | Domain for user subdomains | `harelay.com` |
| `APP_PROXY_PORT` | Port for development (empty for production) | - |
| `APP_PROXY_SECURE` | Use HTTPS for proxy URLs | `true` |
| `TUNNEL_HOST` | Tunnel server bind address | `0.0.0.0` |
| `TUNNEL_PORT` | Tunnel server port (app connections) | `8081` |
| `WS_PROXY_PORT` | WebSocket proxy port | `8082` |
| `WS_PROXY_PATH` | WebSocket path for production (e.g., `/wss`) | - |
| `SESSION_DOMAIN` | Cookie domain (use `.domain.com` for subdomains) | - |
| `REDIS_HOST` | Redis server host (required) | `127.0.0.1` |
| `REDIS_PORT` | Redis server port | `6379` |
| `TUNNEL_DEBUG` | Enable verbose logging | `false` |

## Database Schema

### ha_connections

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| subdomain | string | Unique 8-character subdomain (e.g., "a1b2c3d4") |
| app_subdomain | string | Optional 32-character subdomain for mobile apps |
| connection_token | string | Hashed authentication token |
| status | enum | `connected` or `disconnected` |
| last_connected_at | timestamp | Last heartbeat time |
| bytes_in | bigint | Total bytes uploaded by user |
| bytes_out | bigint | Total bytes downloaded by user |

### device_codes

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| device_code | string | Long code for API polling (64 chars) |
| user_code | string | Short code for user entry (XXXX-XXXX) |
| user_id | bigint | Foreign key to users (nullable until linked) |
| subdomain | string | Assigned subdomain (after linking) |
| connection_token | string | Plain token (temporary, cleared after use) |
| status | enum | `pending`, `linked`, `expired`, `used` |
| expires_at | timestamp | Code expiration time (15 minutes) |

### daily_traffic

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| ha_connection_id | bigint | Foreign key to ha_connections (cascade delete) |
| date | date | Date of the traffic record |
| bytes_in | bigint | Bytes uploaded by user on this date |
| bytes_out | bigint | Bytes downloaded by user on this date |

Unique index on `(ha_connection_id, date)` for efficient upserts. Updated atomically every 30 seconds by the tunnel server.

## Security Considerations

1. **Connection Tokens**: Tokens are hashed in the database and shown only once to users
2. **Session Authentication**: Users must be logged in to access their subdomain
3. **Owner Verification**: Users can only access their own connection
4. **TLS Encryption**: All traffic encrypted in transit (TLS 1.3/1.2)
5. **SSL Labs A+ Rating**: HSTS enabled, modern cipher suites, forward secrecy
6. **No Port Exposure**: Home Assistant never exposes ports to the internet
7. **Token Rotation**: Users can regenerate tokens if compromised
8. **No Crawling**: Subdomain routes include `X-Robots-Tag: noindex` headers
9. **Long Subdomains**: 8-character subdomains (36^8 combinations) prevent brute-force discovery
10. **App Subdomain Security**: 32-character app subdomains (36^32 ≈ 6.3 × 10^49 combinations) make guessing impossible
11. **Device Code Expiry**: Pairing codes expire after 15 minutes and are single-use
12. **Two-Factor Authentication**: Optional 2FA for user accounts
13. **DNS CAA Records**: Restrict certificate issuance to Let's Encrypt only

## Troubleshooting

### App shows "Disconnected"

1. Check the app logs in Home Assistant
2. Verify the connection token is correct
3. Ensure Home Assistant has internet access
4. Check if the tunnel server is running: `systemctl status harelay-tunnel`

### Request Timeout (504)

1. Home Assistant may be slow to respond
2. Check HA logs for errors
3. Verify the app is connected
4. Check tunnel server logs for errors

### WebSocket not working

1. Check if `/api/websocket` path is proxied correctly in Nginx
2. **Critical**: Verify `proxy_set_header Cookie $http_cookie;` is present
3. Verify the tunnel server is running: `systemctl status harelay-tunnel`
4. Check browser console for WebSocket errors
5. Enable `TUNNEL_DEBUG=true` and check logs

### Authentication issues on app_subdomain

1. User must check "Stay logged in" / "Angemeldet bleiben" during HA login
2. This is required for tokens to persist in localStorage
3. Without this checkbox, tokens are memory-only and lost on refresh

### Check Tunnel Server Status

```bash
# View tunnel server logs
sudo journalctl -u harelay-tunnel -f

# View queue worker logs
sudo journalctl -u harelay-queue -f

# Watch Redis activity
redis-cli monitor
```

## Home Assistant App (formerly Add-on)

The HA app is maintained in a separate repository. See the [ha-app](https://github.com/harelay/ha-app) repository for installation and development instructions.

### Key Features
- **MessagePack binary protocol** (40-60% bandwidth reduction vs JSON)
- **No base64 encoding** - raw binary data throughout
- Automatic reconnection with exponential backoff
- Device code pairing mode
- LRU cache for static files (100MB)
- Health check with 60-second timeout
- Ingress WebSocket support for HA apps

## API Endpoints

### Public API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/device/code` | POST | Generate device pairing code |
| `/api/device/poll/{code}` | GET | Poll for pairing completion |
| `/api/connection/status` | GET | Check connection status (auth required) |

### Web Routes

| Route | Description |
|-------|-------------|
| `/link` | Device pairing page |
| `/dashboard` | User dashboard |
| `/dashboard/setup` | Setup guide |
| `/dashboard/settings` | Connection settings |

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-feature`
3. Run tests: `composer test`
4. Format code: `./vendor/bin/pint`
5. Commit your changes
6. Push to the branch: `git push origin feature/my-feature`
7. Submit a pull request

## License

MIT License - see LICENSE file for details.