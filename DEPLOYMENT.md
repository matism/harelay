# HARelay Deployment Guide

This guide covers deploying HARelay on Ubuntu 24.04 LTS with Nginx, including SSL certificates and systemd services.

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Initial Server Setup](#initial-server-setup)
3. [Install Dependencies](#install-dependencies)
4. [Configure MySQL](#configure-mysql)
5. [Deploy the Application](#deploy-the-application)
6. [Configure Nginx](#configure-nginx)
7. [SSL Certificates](#ssl-certificates)
8. [Systemd Services](#systemd-services)
9. [DNS Configuration](#dns-configuration)
10. [Final Steps](#final-steps)
11. [Maintenance](#maintenance)

---

## Server Requirements

- **OS:** Ubuntu 24.04 LTS
- **RAM:** Minimum 1GB (2GB+ recommended)
- **CPU:** 1 vCPU minimum (2+ recommended for production)
- **Storage:** 20GB SSD minimum
- **Ports:** 80 (HTTP redirect), 443 (HTTPS + WebSocket)

---

## Initial Server Setup

### 1. Update the system

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Create a deploy user (optional but recommended)

```bash
sudo adduser deploy
sudo usermod -aG sudo deploy
su - deploy
```

### 3. Configure firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
# Note: Ports 8081 and 8082 are NOT exposed publicly
# They're accessed via Nginx proxy at /tunnel and /wss
sudo ufw enable
```

---

## Install Dependencies

### 1. Install PHP 8.2+ and extensions

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl \
    php8.3-readline php8.3-pcov php8.3-sockets php8.3-redis
```

### 2. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Install Node.js 20+ (for building assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 4. Install MySQL 8

```bash
sudo apt install -y mysql-server

# Secure the installation
sudo mysql_secure_installation
```

### 5. Install Redis

Redis is required for communication between the web server and tunnel server:

```bash
sudo apt install -y redis-server

# Enable and start Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verify it's running
redis-cli ping
# Should return: PONG
```

### 6. Install Nginx

```bash
sudo apt install -y nginx
```

### 7. Install Certbot (for SSL)

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 8. Install Supervisor (for process management)

```bash
sudo apt install -y supervisor
```

---

## Configure MySQL

### 1. Create database and user

```bash
sudo mysql
```

```sql
CREATE DATABASE harelay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'harelay'@'localhost' IDENTIFIED BY 'your-secure-password-here';
GRANT ALL PRIVILEGES ON harelay.* TO 'harelay'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Deploy the Application

HARelay uses a **zero-downtime deployment** structure with symlinks:

```
/var/www/harelay/
├── releases/           # Each deployment creates a new directory here
│   ├── 20240130_120000/
│   ├── 20240130_130000/
│   └── 20240130_140000/  (latest)
├── shared/             # Shared between all releases
│   ├── .env
│   └── storage/
└── current -> releases/20240130_140000/  (symlink to active release)
```

### 1. Create directory structure

```bash
sudo mkdir -p /var/www/harelay/{releases,shared}
sudo mkdir -p /var/www/harelay/shared/storage/{app/public,framework/{cache,sessions,views},logs}
sudo chown -R www-data:www-data /var/www/harelay/shared
```

### 2. Set up deploy user SSH key

```bash
# Switch to deploy user
sudo su - deploy

# Generate SSH key for GitHub
ssh-keygen -t ed25519 -C "deploy@harelay"

# Show public key - add this to GitHub as a deploy key
cat ~/.ssh/id_ed25519.pub

# Test connection
ssh -T git@github.com
```

### 3. Initial clone

```bash
# As deploy user
cd /var/www/harelay/releases
git clone git@github.com:YOUR_USERNAME/harelay.git initial
```

### 4. Configure environment

```bash
sudo nano /var/www/harelay/shared/.env
```

```env
APP_NAME=HARelay
APP_ENV=production
APP_DEBUG=false
APP_URL=https://harelay.com

# Proxy settings
APP_PROXY_DOMAIN=harelay.com
APP_PROXY_PORT=
APP_PROXY_SECURE=true

# Session domain for subdomain cookies
SESSION_DOMAIN=.harelay.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=harelay
DB_USERNAME=harelay
DB_PASSWORD=your-secure-password-here

# Cache and Session
CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Redis (required for tunnel IPC and caching)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Tunnel Server
TUNNEL_HOST=0.0.0.0
TUNNEL_PORT=8081
WS_PROXY_PORT=8082
WS_PROXY_PATH=/wss
TUNNEL_DEBUG=false

# Mail (configure for your provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@harelay.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Link shared files and set up initial release

```bash
cd /var/www/harelay/releases/initial

# Remove storage and link to shared
rm -rf storage
ln -s /var/www/harelay/shared/storage storage

# Link .env
rm -f .env
ln -s /var/www/harelay/shared/.env .env

# Fix symlink ownership
sudo chown -h www-data:www-data storage .env

# Fix directory ownership
sudo chown -R www-data:www-data /var/www/harelay/releases/initial
```

### 6. Install dependencies and build

```bash
cd /var/www/harelay/releases/initial

# PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Node dependencies and build
sudo -u www-data npm install
sudo -u www-data npm run build
```

### 7. Generate app key and run migrations

```bash
cd /var/www/harelay/releases/initial
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force
```

### 8. Create current symlink

```bash
sudo ln -sfn /var/www/harelay/releases/initial /var/www/harelay/current
```

### 9. Optimize for production

```bash
cd /var/www/harelay/current
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## Configure Nginx

### 1. Create Nginx configuration

```bash
sudo nano /etc/nginx/sites-available/harelay
```

```nginx
# Main application and wildcard subdomains
server {
    listen 80;
    listen [::]:80;
    server_name harelay.com *.harelay.com;

    # Redirect to HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name harelay.com *.harelay.com;

    root /var/www/harelay/current/public;
    index index.php;

    # SSL certificates (will be configured by Certbot)
    ssl_certificate /etc/letsencrypt/live/harelay.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/harelay.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    # Max upload size
    client_max_body_size 10M;

    # Logging
    access_log /var/log/nginx/harelay.access.log;
    error_log /var/log/nginx/harelay.error.log;

    # Tunnel WebSocket (for Home Assistant add-on connections)
    location /tunnel {
        proxy_pass http://127.0.0.1:8081;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
    }

    # Browser WebSocket proxy (for Home Assistant real-time features)
    location /wss {
        proxy_pass http://127.0.0.1:8082;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
        proxy_send_timeout 86400;
    }

    # PHP handling
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(\.env|composer\.(json|lock)|package\.json|webpack\.mix\.js) {
        deny all;
    }
}
```

### 2. Enable the site

```bash
sudo ln -s /etc/nginx/sites-available/harelay /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default  # Remove default site
sudo nginx -t
sudo systemctl reload nginx
```

---

## SSL Certificates

### 1. Obtain certificates with Certbot

For wildcard certificates (required for subdomains), you need DNS validation:

```bash
sudo certbot certonly --manual --preferred-challenges=dns \
    -d harelay.com -d "*.harelay.com"
```

Follow the prompts to add DNS TXT records for validation.

**Alternative: Using Cloudflare DNS plugin (recommended for automation):**

```bash
sudo apt install -y python3-certbot-dns-cloudflare

# Create Cloudflare credentials file
sudo mkdir -p /etc/letsencrypt
sudo nano /etc/letsencrypt/cloudflare.ini
```

Add your Cloudflare API credentials:
```ini
dns_cloudflare_api_token = your-cloudflare-api-token
```

```bash
sudo chmod 600 /etc/letsencrypt/cloudflare.ini

# Obtain certificate
sudo certbot certonly --dns-cloudflare \
    --dns-cloudflare-credentials /etc/letsencrypt/cloudflare.ini \
    -d harelay.com -d "*.harelay.com"
```

### 2. Auto-renewal

Certbot automatically sets up renewal. Test it:

```bash
sudo certbot renew --dry-run
```

---

## Systemd Services

### 1. Tunnel Server Service

Create the tunnel server service:

```bash
sudo nano /etc/systemd/system/harelay-tunnel.service
```

```ini
[Unit]
Description=HARelay Tunnel Server
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/harelay/current
ExecStart=/usr/bin/php /var/www/harelay/current/tunnel-server.php start
Restart=always
RestartSec=5
StandardOutput=append:/var/log/harelay/tunnel.log
StandardError=append:/var/log/harelay/tunnel-error.log

[Install]
WantedBy=multi-user.target
```

### 2. Queue Worker Service

Create the queue worker service:

```bash
sudo nano /etc/systemd/system/harelay-queue.service
```

```ini
[Unit]
Description=HARelay Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/harelay/current
ExecStart=/usr/bin/php /var/www/harelay/current/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5
StandardOutput=append:/var/log/harelay/queue.log
StandardError=append:/var/log/harelay/queue-error.log

[Install]
WantedBy=multi-user.target
```

### 3. Create log directory and enable services

```bash
sudo mkdir -p /var/log/harelay
sudo chown www-data:www-data /var/log/harelay

sudo systemctl daemon-reload
sudo systemctl enable harelay-tunnel
sudo systemctl enable harelay-queue
sudo systemctl start harelay-tunnel
sudo systemctl start harelay-queue
```

### 4. Check service status

```bash
sudo systemctl status harelay-tunnel
sudo systemctl status harelay-queue
```

---

## DNS Configuration

Configure DNS records for your domain:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | @ | Your server IP | 300 |
| A | * | Your server IP | 300 |
| AAAA | @ | Your server IPv6 (if available) | 300 |
| AAAA | * | Your server IPv6 (if available) | 300 |

The wildcard (`*`) record is essential for subdomain routing.

---

## Final Steps

### 1. Test the deployment

```bash
# Check if the application responds
curl -I https://harelay.com

# Check if tunnel server is running
sudo systemctl status harelay-tunnel

# Check logs for errors
sudo tail -f /var/log/harelay/tunnel.log
sudo tail -f /var/log/nginx/harelay.error.log
```

### 2. Create a test user

```bash
cd /var/www/harelay/current
sudo -u www-data php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
```

### 3. Verify add-on can connect

Test that the Home Assistant add-on can connect to port 8081:

```bash
# From another machine
nc -zv harelay.com 8081
```

---

## Maintenance

### Deploying Updates (Zero-Downtime)

Use the included deploy script for zero-downtime updates:

```bash
sudo /var/www/harelay/current/deploy.sh
```

The deploy script handles:
- Creates a new release directory
- Copies from current release (faster than fresh clone)
- Updates code via git
- Links shared `.env` and `storage`
- Installs Composer and npm dependencies
- Builds frontend assets
- Runs database migrations
- Caches configuration
- **Atomically switches symlink** (zero downtime!)
- Restarts services (tunnel, queue, PHP-FPM)
- Cleans up old releases (keeps last 5)

**Benefits:**
- Site stays up during entire deployment
- Instant rollback capability
- No maintenance mode needed

### Rollback to Previous Release

If something goes wrong, instantly rollback:

```bash
# List available releases
ls -la /var/www/harelay/releases/

# Rollback to a previous release
sudo ln -sfn /var/www/harelay/releases/PREVIOUS_RELEASE /var/www/harelay/current
sudo systemctl reload php8.3-fpm
sudo systemctl restart harelay-queue harelay-tunnel
```

### Viewing Logs

```bash
# Application logs (in shared storage)
sudo tail -f /var/www/harelay/shared/storage/logs/laravel.log

# Tunnel server logs
sudo tail -f /var/log/harelay/tunnel.log

# Queue worker logs
sudo tail -f /var/log/harelay/queue.log

# Nginx logs
sudo tail -f /var/log/nginx/harelay.error.log
```

### Log Rotation

Create logrotate configuration:

```bash
sudo nano /etc/logrotate.d/harelay
```

```
/var/log/harelay/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload harelay-tunnel > /dev/null 2>&1 || true
    endscript
}

/var/www/harelay/shared/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### Database Backups

Create a backup script:

```bash
sudo nano /usr/local/bin/backup-harelay.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/harelay"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u harelay -p'your-password' harelay > "$BACKUP_DIR/db_$DATE.sql"

# Compress
gzip "$BACKUP_DIR/db_$DATE.sql"

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-harelay.sh

# Add to crontab
sudo crontab -e
```

Add:
```
0 2 * * * /usr/local/bin/backup-harelay.sh
```

---

## Troubleshooting

### Common Issues

**1. 502 Bad Gateway**
- Check if PHP-FPM is running: `sudo systemctl status php8.3-fpm`
- Check PHP-FPM logs: `sudo tail -f /var/log/php8.3-fpm.log`

**2. Tunnel not connecting**
- Verify port 8081 is open: `sudo ufw status`
- Check tunnel service: `sudo systemctl status harelay-tunnel`
- Check tunnel logs: `sudo tail -f /var/log/harelay/tunnel.log`

**3. WebSocket not working**
- Verify Nginx proxies /wss correctly
- Check browser console for WebSocket errors
- Ensure port 8082 is accessible

**4. Subdomains not working**
- Verify wildcard DNS record exists
- Check Nginx configuration for *.harelay.com
- Verify wildcard SSL certificate covers *.harelay.com

**5. Redis not working**
- Check if Redis is running: `sudo systemctl status redis-server`
- Test connection: `redis-cli ping` (should return PONG)
- Check PHP Redis extension: `php -m | grep redis`
- View Redis logs: `sudo tail -f /var/log/redis/redis-server.log`

**6. Permission errors**
```bash
sudo chown -R www-data:www-data /var/www/harelay/current
sudo chown -R www-data:www-data /var/www/harelay/shared
sudo chmod -R 755 /var/www/harelay/current
sudo chmod -R 775 /var/www/harelay/shared/storage
sudo chmod -R 775 /var/www/harelay/current/bootstrap/cache
```

---

## Security Checklist

- [ ] Firewall enabled and configured
- [ ] SSL certificates installed and auto-renewing
- [ ] APP_DEBUG=false in production
- [ ] Strong database password
- [ ] Regular backups configured
- [ ] Log rotation configured
- [ ] SSH key authentication (disable password auth)
- [ ] Fail2ban installed (optional but recommended)
- [ ] Regular security updates (`sudo apt update && sudo apt upgrade`)

---

## Additional Production Considerations

### Email Configuration

For password reset and email verification, configure a mail provider in `.env`:

**Using Mailgun:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.harelay.com
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@harelay.com"
MAIL_FROM_NAME="HARelay"
```

**Using Amazon SES:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-west-1
MAIL_FROM_ADDRESS="noreply@harelay.com"
```

### Monitoring (Optional)

**Uptime Monitoring:**
- Use services like UptimeRobot, Pingdom, or Better Uptime
- Monitor: `https://harelay.com` and `https://harelay.com/api/device/code` (should return 405)

**Error Tracking:**
- Consider integrating Sentry for error tracking
- Install: `composer require sentry/sentry-laravel`

**Server Monitoring:**
- Netdata: `bash <(curl -Ss https://get.netdata.cloud/kickstart.sh)`
- Or use your hosting provider's monitoring

### Performance Tuning

**PHP-FPM Configuration:**
```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

For a 2GB server:
```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
pm.max_requests = 500
```

**MySQL Tuning:**
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add under `[mysqld]`:
```ini
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 100
```

### Scaling Considerations

The tunnel server (Workerman) can handle thousands of concurrent connections on a single server. If you need to scale beyond that:

1. **Vertical Scaling:** Increase server RAM/CPU
2. **Horizontal Scaling:**
   - Use a load balancer (HAProxy, Nginx)
   - Run multiple tunnel server instances
   - Use Redis for shared state between instances

### Add-on Repository

Publish your add-on repository:

1. Create a GitHub repository at `github.com/harelay/ha-addon`
2. Include `repository.json` at the root:

```json
{
  "name": "HARelay Add-ons",
  "url": "https://github.com/harelay/ha-addon",
  "maintainer": "HARelay <support@harelay.com>"
}
```

3. Include your add-on in a subdirectory (e.g., `harelay/`)

### Rate Limiting

Laravel's default rate limiting is configured in `app/Providers/AppServiceProvider.php`. The API endpoints already have rate limiting (60 requests/minute by default). Adjust if needed:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Health Checks

Add a health check endpoint for monitoring:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

### Backup Strategy

In addition to database backups, consider:

1. **Configuration backup:** `/var/www/harelay/shared/.env`
2. **SSL certificates:** `/etc/letsencrypt/`
3. **Nginx config:** `/etc/nginx/sites-available/harelay`

Full backup script:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d)
BACKUP_DIR="/var/backups/harelay"

mkdir -p $BACKUP_DIR/$DATE

# Database
mysqldump -u harelay -p'password' harelay | gzip > "$BACKUP_DIR/$DATE/db.sql.gz"

# Environment (from shared directory)
cp /var/www/harelay/shared/.env "$BACKUP_DIR/$DATE/.env"

# Keep 30 days
find $BACKUP_DIR -maxdepth 1 -type d -mtime +30 -exec rm -rf {} \;
```
