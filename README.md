# HARelay

Secure remote access proxy for Home Assistant. Access your smart home from anywhere without port forwarding.

## Overview

HARelay provides a secure tunnel between your Home Assistant instance and the internet. Users install a lightweight add-on on their Home Assistant, which establishes an outbound WebSocket connection to the HARelay server. This allows remote access without exposing any ports on your home network.

**Key Features:**
- No port forwarding required
- Works behind CGNAT and firewalls
- Unique subdomain per user (e.g., `yourname.harelay.com`)
- End-to-end encryption via TLS
- Session-based authentication
- Full WebSocket support for real-time Home Assistant features

## Architecture

```
┌─────────────────┐     HTTPS/WSS     ┌──────────────────────────────────┐
│  User Browser   │ ◄───────────────► │         HARelay Server           │
│                 │                   │  - Laravel App (HTTP proxy)      │
└─────────────────┘                   │  - Workerman Tunnel Server       │
        │                             │  - WebSocket Proxy               │
        │                             └──────────────────────────────────┘
        │ visits subdomain.harelay.com           │
        │                                        │ WebSocket tunnel
        │                             ┌──────────────────────────────────┐
        └────────────────────────────►│      Home Assistant Add-on       │
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

1. **User Registration**: User creates an account on HARelay and receives a unique subdomain and connection token
2. **Add-on Installation**: User installs the HARelay add-on on Home Assistant and configures it with the connection token
3. **Tunnel Establishment**: The add-on connects to HARelay via WebSocket (outbound connection, no ports needed)
4. **Remote Access**: User visits their subdomain, authenticates, and requests are proxied through the tunnel to Home Assistant

## Requirements

- PHP 8.2+
- Composer
- Node.js 20.19+ or 22.12+ (for Vite)
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

## Production Deployment (Ubuntu/DigitalOcean)

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2+ and extensions
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-bcmath php8.2-sqlite3

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 22
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 2. Application Setup

```bash
# Create web directory
sudo mkdir -p /var/www/harelay
sudo chown $USER:$USER /var/www/harelay

# Clone and install
cd /var/www/harelay
git clone https://github.com/your-org/harelay.git .
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Environment
cp .env.example .env
php artisan key:generate
```

### 3. Configure Environment

Edit `/var/www/harelay/.env`:

```env
APP_NAME=HARelay
APP_ENV=production
APP_DEBUG=false
APP_URL=https://harelay.com
APP_PROXY_DOMAIN=harelay.com
APP_PROXY_PORT=
APP_PROXY_SECURE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=harelay
DB_USERNAME=harelay
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
SESSION_DOMAIN=.harelay.com

CACHE_STORE=database
QUEUE_CONNECTION=database

# Tunnel server
TUNNEL_HOST=0.0.0.0
TUNNEL_PORT=8081
WS_PROXY_PORT=8082
WS_PROXY_PATH=/wss
TUNNEL_DEBUG=false
```

### 4. Database Setup

```bash
# Create database and user
sudo mysql -u root -p <<EOF
CREATE DATABASE harelay;
CREATE USER 'harelay'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON harelay.* TO 'harelay'@'localhost';
FLUSH PRIVILEGES;
EOF

# Run migrations
php artisan migrate --force
```

### 5. SSL Certificates (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get certificates (main domain)
sudo certbot certonly --nginx -d harelay.com -d www.harelay.com

# Get wildcard certificate (requires DNS validation)
sudo certbot certonly --manual --preferred-challenges=dns \
    -d "*.harelay.com" --agree-tos
```

### 6. Nginx Configuration

Create `/etc/nginx/sites-available/harelay`:

```nginx
# Main domain (harelay.com)
server {
    listen 80;
    server_name harelay.com www.harelay.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name harelay.com www.harelay.com;

    ssl_certificate /etc/letsencrypt/live/harelay.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/harelay.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    root /var/www/harelay/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Wildcard subdomains (*.harelay.com)
server {
    listen 80;
    server_name *.harelay.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name *.harelay.com;

    ssl_certificate /etc/letsencrypt/live/harelay.com-0001/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/harelay.com-0001/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    root /var/www/harelay/public;
    index index.php;

    add_header X-Robots-Tag "noindex, nofollow";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    # WebSocket proxy path
    location /wss {
        proxy_pass http://127.0.0.1:8082;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_read_timeout 86400;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/harelay /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Tunnel Server Service

Create `/etc/systemd/system/harelay-tunnel.service`:

```ini
[Unit]
Description=HARelay Tunnel Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/harelay
ExecStart=/usr/bin/php tunnel-server.php start
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable harelay-tunnel
sudo systemctl start harelay-tunnel
```

### 8. Queue Worker Service

Create `/etc/systemd/system/harelay-queue.service`:

```ini
[Unit]
Description=HARelay Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/harelay
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl daemon-reload
sudo systemctl enable harelay-queue
sudo systemctl start harelay-queue
```

### 9. File Permissions

```bash
sudo chown -R www-data:www-data /var/www/harelay
sudo chmod -R 755 /var/www/harelay
sudo chmod -R 775 /var/www/harelay/storage
sudo chmod -R 775 /var/www/harelay/bootstrap/cache
```

### 10. Firewall Configuration

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8081/tcp  # Tunnel server (add-on connections)
sudo ufw enable
```

### Deployment Updates

```bash
cd /var/www/harelay
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart harelay-tunnel
sudo systemctl restart harelay-queue
sudo systemctl reload php8.2-fpm
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_PROXY_DOMAIN` | Domain for user subdomains | `harelay.com` |
| `APP_PROXY_PORT` | Port for development (empty for production) | - |
| `APP_PROXY_SECURE` | Use HTTPS for proxy URLs | `true` |
| `TUNNEL_HOST` | Tunnel server bind address | `0.0.0.0` |
| `TUNNEL_PORT` | Tunnel server port (add-on connections) | `8081` |
| `WS_PROXY_PORT` | WebSocket proxy port | `8082` |
| `WS_PROXY_PATH` | WebSocket path for production (e.g., `/wss`) | - |
| `SESSION_DOMAIN` | Cookie domain (use `.domain.com` for subdomains) | - |

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

## Security Considerations

1. **Connection Tokens**: Tokens are hashed in the database and shown only once to users
2. **Session Authentication**: Users must be logged in to access their subdomain
3. **Owner Verification**: Users can only access their own connection
4. **TLS Encryption**: All traffic encrypted in transit
5. **No Port Exposure**: Home Assistant never exposes ports to the internet
6. **Token Rotation**: Users can regenerate tokens if compromised
7. **No Crawling**: Subdomain routes include `X-Robots-Tag: noindex` headers

## Troubleshooting

### Add-on shows "Disconnected"

1. Check the add-on logs in Home Assistant
2. Verify the connection token is correct
3. Ensure Home Assistant has internet access
4. Check if port 8081 is accessible on the server

### Request Timeout (504)

1. Home Assistant may be slow to respond
2. Check HA logs for errors
3. Verify the add-on is connected

### WebSocket not working

1. Check if `/wss` path is proxied correctly in Nginx
2. Verify the tunnel server is running: `systemctl status harelay-tunnel`
3. Check browser console for WebSocket errors

### Check Tunnel Server Status

```bash
# View tunnel server logs
sudo journalctl -u harelay-tunnel -f

# View queue worker logs
sudo journalctl -u harelay-queue -f
```

## Home Assistant Add-on

The HA add-on is maintained in a separate repository. See the [harelay-addon](https://github.com/harelay/harelay-addon) repository for installation and development instructions.

## License

MIT License - see LICENSE file for details.