# Security Review

Last reviewed: 2026-02-07

## Infrastructure

- **Firewall**: Only ports 80 and 443 are open (DigitalOcean firewall + UFW). Tunnel ports 8081/8082 are not directly accessible.
- **TLS**: Nginx terminates TLS. Backend communication (nginx <-> Workerman) is localhost-only (`127.0.0.1`), which is the standard pattern for reverse-proxied backends.
- **Redis**: Accessible only from localhost. Used for tunnel IPC with igbinary serialization + LZ4 compression.

## Authentication & Authorization

### Connection Tokens
- Generated with `Str::random(64)` (cryptographically secure via `random_bytes()`)
- Stored as **bcrypt hashes** — plaintext never persisted
- Shown to user only once via flash session data
- Verified with `Hash::check()` in the tunnel server

### Subdomain Ownership
- **Regular subdomains**: Require HARelay login + ownership check (`user_id` match)
- **App subdomains**: 32-character random string (36^32 = 6.3x10^49 combinations) — URL itself is the authentication
- `ProxyController` enforces ownership for regular subdomains on every request
- Tunnel server WebSocket proxy verifies ownership via encrypted session cookie

### Session Security
- Session cookie is always encrypted by Laravel's `EncryptCookies` middleware
- Session domain set to `.harelay.com` for subdomain cookie sharing
- Subdomain proxy **reads** sessions but never **writes** cookies back (prevents logout on main domain)
- HTTP-only, Secure, SameSite=Lax flags on session cookies

### WebSocket Proxy (Port 8082)
- Only reachable through nginx (port not exposed in firewall)
- Nginx forwards `Cookie` header, enabling transparent session-based authentication
- Main path (`/api/websocket`): Verifies session cookie + subdomain ownership
- App subdomain path: No HARelay auth (by design — HA auth still required)
- Ingress path (`/api/hassio_ingress/{token}/ws`): Passes through `ingress_session` cookie; HA validates it on its end

### Device Code Pairing
- User codes: `XXXX-XXXX` format (36^8 = ~2.8 trillion combinations)
- Codes expire after 15 minutes
- `POST /link` rate limited to 10 requests/minute (max 150 attempts in the 15-min window — not brute-forceable)
- Plain token stored temporarily in `device_codes` table between linking and first poll, then cleared

## Fixes Applied

### Per-Subdomain Proxy Rate Limiting (2026-02-07)

**Problem**: No rate limiting on proxied subdomain requests. An attacker with a valid session could flood a user's HA instance with unlimited requests.

**Fix**: Added per-subdomain rate limiting (300 requests/minute) in `SubdomainProxy` middleware. Uses Laravel's `RateLimiter`, keyed by subdomain. Returns `429 Too Many Requests` with `Retry-After` header when exceeded.

**Why 300/min**: HA's frontend loads 50-80 resources on a cold page load. 300/min allows multiple rapid reloads during troubleshooting while still blocking scripted abuse (which would be thousands/second).

**File**: `app/Http/Middleware/SubdomainProxy.php`

## Reviewed & Accepted Risks

### Subdomain Enumeration
Different HTTP status codes (404 vs 401 vs 503) reveal whether a subdomain exists. **Accepted** because:
- Auto-generated subdomains have 36^8 = ~2.8 trillion combinations — not enumerable at scale
- Knowing a subdomain exists is useless without a valid HARelay session (regular) or the 32-char app_subdomain
- Unifying error pages would significantly hurt UX (user wouldn't know if they need to log in vs. their HA is disconnected)

### Session Payload Not Encrypted in Database
The `sessions.payload` column stores base64-encoded PHP-serialized data, not encrypted. **Accepted** because:
- Session data only contains `user_id`, CSRF token, and flash data
- Database access already exposes the `users` table directly — encrypting session data adds no meaningful protection
- Adds per-request encrypt/decrypt overhead for no security benefit

### Port 8081 Uses `ws://` Not `wss://`
Workerman listens on unencrypted WebSocket. **Accepted** because:
- Port 8081 is not exposed in the firewall — only reachable from localhost
- Nginx terminates TLS and proxies to localhost — traffic never touches the network
- Adding TLS to Workerman would add handshake overhead and certificate management for zero security benefit

### `can_set_subdomain` in User `$fillable`
This flag grants permission for custom subdomains. It's in the `$fillable` array, which could be a privilege escalation risk if future code carelessly mass-assigns user input. **Accepted** because:
- No current code path allows users to set this via mass assignment
- All user-facing forms explicitly specify which fields to update
- Worth keeping in mind when adding new user update endpoints

### Redis IPC Unsigned
Communication between Laravel and the tunnel server via Redis has no message signing. **Accepted** because:
- Redis is only accessible from localhost
- If an attacker has Redis access, they already have server access — signing wouldn't help
- Standard architecture for Redis-backed IPC

### Non-Transparent WebSocket Auth Path
The fallback auth path on port 8082 (JSON `{"type": "auth", "subdomain": "..."}`) has no ownership verification. **Accepted** because:
- Port 8082 is not exposed in the firewall
- Nginx always forwards cookies, so the transparent (cookie-based) auth path runs first
- This fallback code path is effectively unreachable in production

### Token Theft Requires Physical Access
Stealing a connection token requires either:
- Physical/SSH access to the HA device (read `/data/credentials.json` in the add-on container)
- Database access during the brief device-pairing window (seconds)
- Seeing the token on screen when first displayed in the dashboard

None of these are remote attack vectors.
