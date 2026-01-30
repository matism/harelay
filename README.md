# HARelay

Secure remote access proxy for Home Assistant. Access your smart home from anywhere without port forwarding.

## Overview

HARelay provides a secure tunnel between your Home Assistant instance and the internet. Users install a lightweight add-on on their Home Assistant, which establishes an outbound WebSocket connection to the HARelay server. This allows remote access without exposing any ports on your home network.

**Key Features:**
- No port forwarding required
- Works behind CGNAT and firewalls
- Unique subdomain per user (e.g., `yourname.harelay.io`)
- End-to-end encryption via TLS
- Session-based authentication

## Architecture

```
┌─────────────────┐     HTTPS        ┌──────────────────────────────────┐
│  User Browser   │ ◄──────────────► │         HARelay Server           │
│                 │                  │  - Laravel App (Marketing/Auth)  │
└─────────────────┘                  │  - Reverb WebSocket Server       │
        │                            │  - Proxy Controller              │
        │                            └──────────────────────────────────┘
        │                                       │
        │ visits subdomain.harelay.io           │ WebSocket + HTTP API
        │                                       │
        │                            ┌──────────────────────────────────┐
        └──────────────────────────► │      Home Assistant Add-on       │
                                     │  - Connects via WebSocket        │
                                     │  - Receives proxy requests       │
                                     │  - Forwards to local HA          │
                                     └──────────────────────────────────┘
                                                │
                                                ▼
                                     ┌──────────────────────────────────┐
                                     │      Home Assistant Instance     │
                                     │      (localhost:8123)            │
                                     └──────────────────────────────────┘
```

### How It Works

1. **User Registration**: User creates an account on HARelay and receives a unique subdomain and connection token
2. **Add-on Installation**: User installs the HARelay add-on on Home Assistant and configures it with the connection token
3. **Tunnel Establishment**: The add-on connects to HARelay via WebSocket (outbound connection, no ports needed)
4. **Remote Access**: User visits their subdomain, authenticates, and requests are proxied through the tunnel to Home Assistant

## Requirements

- PHP 8.2+
- Composer
- Node.js 20.19+ or 22.12+ (for Vite)
- MySQL/PostgreSQL (production) or SQLite (development)

## Installation

### 1. Clone and Install Dependencies

```bash
git clone https://github.com/your-org/harelay.git
cd harelay
composer install
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Environment

Edit `.env` with your settings:

```env
APP_NAME=HARelay
APP_URL=https://harelay.io
APP_PROXY_DOMAIN=harelay.io

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=harelay
DB_USERNAME=your_user
DB_PASSWORD=your_password

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Build Assets

```bash
npm run build
```

## Development

Start all development services with a single command:

```bash
composer dev
```

This runs concurrently:
- **server**: Laravel development server (port 8000)
- **queue**: Queue worker for background jobs
- **logs**: Laravel Pail for real-time log viewing
- **vite**: Vite dev server for hot reloading
- **reverb**: WebSocket server (port 8080)

### Running Tests

```bash
composer test
```

### Code Formatting

```bash
./vendor/bin/pint
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_PROXY_DOMAIN` | Domain for user subdomains | `harelay.io` |
| `REVERB_HOST` | WebSocket server hostname | `localhost` |
| `REVERB_PORT` | WebSocket server port | `8080` |
| `REVERB_SCHEME` | WebSocket protocol (`http`/`https`) | `http` |

### Production Configuration

For production, ensure:

1. **TLS Certificates**: Configure SSL for both the main domain and wildcard subdomain (`*.harelay.io`)
2. **Reverb TLS**: Set `REVERB_SCHEME=https` and configure TLS in `config/reverb.php`
3. **Web Server**: Configure nginx/Apache to route wildcard subdomains to Laravel

Example nginx configuration for wildcard subdomains:

```nginx
server {
    listen 443 ssl;
    server_name *.harelay.io;

    ssl_certificate /path/to/wildcard.crt;
    ssl_certificate_key /path/to/wildcard.key;

    root /var/www/harelay/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## API Reference

### Tunnel API (for HA Add-on)

All endpoints require `subdomain` and `token` in the request body.

#### Connect

Register the add-on with the server.

```http
POST /api/tunnel/connect
Content-Type: application/json

{
    "subdomain": "abc123",
    "token": "your-connection-token"
}
```

Response:
```json
{
    "success": true,
    "subdomain": "abc123",
    "websocket": {
        "host": "harelay.io",
        "port": 443,
        "scheme": "https",
        "key": "reverb-app-key",
        "channel": "private-tunnel.abc123"
    },
    "api": {
        "auth_endpoint": "https://harelay.io/api/tunnel/auth",
        "response_endpoint": "https://harelay.io/api/tunnel/response",
        "heartbeat_endpoint": "https://harelay.io/api/tunnel/heartbeat"
    }
}
```

#### WebSocket Channel Auth

Authenticate for the private WebSocket channel.

```http
POST /api/tunnel/auth
Content-Type: application/json

{
    "socket_id": "123456.789",
    "channel_name": "private-tunnel.abc123",
    "subdomain": "abc123",
    "token": "your-connection-token"
}
```

#### Heartbeat

Keep the connection alive (call every 30-60 seconds).

```http
POST /api/tunnel/heartbeat
Content-Type: application/json

{
    "subdomain": "abc123",
    "token": "your-connection-token"
}
```

#### Submit Response

Submit a response for a proxied request.

```http
POST /api/tunnel/response
Content-Type: application/json

{
    "subdomain": "abc123",
    "token": "your-connection-token",
    "request_id": "uuid-of-request",
    "status_code": 200,
    "headers": {
        "Content-Type": "text/html"
    },
    "body": "<html>...</html>"
}
```

#### Poll Requests (Fallback)

Alternative to WebSocket for receiving requests.

```http
POST /api/tunnel/poll
Content-Type: application/json

{
    "subdomain": "abc123",
    "token": "your-connection-token"
}
```

### WebSocket Events

The add-on subscribes to `private-tunnel.{subdomain}` and receives:

#### tunnel.request

Fired when a user makes a request to their subdomain.

```json
{
    "request_id": "550e8400-e29b-41d4-a716-446655440000",
    "method": "GET",
    "uri": "/api/states",
    "headers": {
        "accept": "application/json",
        "user-agent": "Mozilla/5.0..."
    },
    "body": null
}
```

## Database Schema

### ha_connections

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| subdomain | string | Unique subdomain (e.g., "abc123") |
| connection_token | string | Hashed authentication token |
| status | enum | `connected` or `disconnected` |
| last_connected_at | timestamp | Last heartbeat time |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |

### subscriptions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| plan | enum | `free`, `monthly`, `annual` |
| status | enum | `active`, `cancelled`, `expired` |
| trial_ends_at | timestamp | Trial expiration (nullable) |
| expires_at | timestamp | Subscription expiration (nullable) |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |

## Project Structure

```
app/
├── Events/
│   ├── TunnelConnected.php      # Fired when add-on connects
│   ├── TunnelDisconnected.php   # Fired when add-on disconnects
│   └── TunnelRequest.php        # Broadcast to add-on for proxy requests
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── TunnelApiController.php   # Add-on API endpoints
│   │   │   └── TunnelAuthController.php  # WebSocket auth
│   │   ├── ConnectionController.php      # User connection management
│   │   ├── DashboardController.php       # User dashboard
│   │   ├── MarketingController.php       # Public pages
│   │   └── ProxyController.php           # HTTP request proxying
│   └── Middleware/
│       ├── CheckSubscription.php         # Subscription validation
│       └── ProxyMiddleware.php           # Subdomain detection
├── Models/
│   ├── HaConnection.php         # Connection model
│   ├── Subscription.php         # Subscription model
│   └── User.php                 # User model (with relationships)
└── Services/
    └── TunnelManager.php        # Tunnel orchestration service

resources/views/
├── components/
│   └── marketing-layout.blade.php
├── dashboard/
│   ├── index.blade.php          # Connection status
│   ├── setup.blade.php          # Setup instructions
│   ├── settings.blade.php       # Token management
│   └── subscription.blade.php   # Plan details
├── errors/
│   ├── auth-required.blade.php
│   ├── tunnel-disconnected.blade.php
│   └── tunnel-timeout.blade.php
└── marketing/
    ├── home.blade.php           # Landing page
    ├── how-it-works.blade.php   # Setup guide
    └── pricing.blade.php        # Plans comparison

routes/
├── api.php                      # Tunnel API routes
├── channels.php                 # WebSocket channel auth
└── web.php                      # Web routes + subdomain proxy
```

## Home Assistant Add-on Development

The HA add-on needs to:

1. **Connect to HARelay API** with subdomain and token
2. **Subscribe to WebSocket channel** for receiving requests
3. **Send heartbeats** every 30-60 seconds
4. **Handle tunnel.request events** by making local HTTP requests to Home Assistant
5. **Submit responses** via the API

### Example Add-on Flow (Python)

```python
import asyncio
import aiohttp
import json
from pusher import Pusher

class HARelayTunnel:
    def __init__(self, subdomain, token, server_url):
        self.subdomain = subdomain
        self.token = token
        self.server_url = server_url
        self.ha_url = "http://supervisor/core"

    async def connect(self):
        # Register with server
        async with aiohttp.ClientSession() as session:
            async with session.post(
                f"{self.server_url}/api/tunnel/connect",
                json={"subdomain": self.subdomain, "token": self.token}
            ) as resp:
                data = await resp.json()
                self.ws_config = data["websocket"]

        # Connect to WebSocket
        self.pusher = Pusher(
            app_id=self.ws_config["key"],
            host=self.ws_config["host"],
            port=self.ws_config["port"]
        )

        channel = self.pusher.subscribe(self.ws_config["channel"])
        channel.bind("tunnel.request", self.handle_request)

    async def handle_request(self, data):
        # Forward to Home Assistant
        async with aiohttp.ClientSession() as session:
            async with session.request(
                method=data["method"],
                url=f"{self.ha_url}{data['uri']}",
                headers=data["headers"],
                data=data.get("body")
            ) as resp:
                body = await resp.text()

                # Submit response
                await session.post(
                    f"{self.server_url}/api/tunnel/response",
                    json={
                        "subdomain": self.subdomain,
                        "token": self.token,
                        "request_id": data["request_id"],
                        "status_code": resp.status,
                        "headers": dict(resp.headers),
                        "body": body
                    }
                )
```

## Security Considerations

1. **Connection Tokens**: Tokens are hashed in the database and shown only once to users
2. **Session Authentication**: Users must be logged in to access their subdomain
3. **Owner Verification**: Users can only access their own connection
4. **TLS Encryption**: All traffic encrypted in transit
5. **No Port Exposure**: Home Assistant never exposes ports to the internet
6. **Token Rotation**: Users can regenerate tokens if compromised

## Troubleshooting

### Add-on shows "Disconnected"

1. Check the add-on logs in Home Assistant
2. Verify the connection token is correct
3. Ensure Home Assistant has internet access
4. Check if the HARelay server is reachable

### Request Timeout (504)

1. Home Assistant may be slow to respond
2. Check HA logs for errors
3. Try a simpler request (e.g., `/api/`)
4. Verify the add-on is connected

### Authentication Required

1. Make sure you're logged into HARelay
2. Clear browser cookies and try again
3. Verify the subdomain matches your account

## License

MIT License - see LICENSE file for details.
