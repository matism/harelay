<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HaConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subdomain',
        'app_subdomain',
        'connection_token',
        'status',
        'last_connected_at',
        'bytes_in',
        'bytes_out',
    ];

    protected function casts(): array
    {
        return [
            'last_connected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /**
     * Find a connection by subdomain (regular or app).
     * Returns the connection and whether it was found via app_subdomain.
     *
     * @return array{connection: HaConnection|null, is_app_subdomain: bool}
     */
    public static function findBySubdomain(string $subdomain): array
    {
        // First check regular subdomain (more common case)
        $connection = self::where('subdomain', $subdomain)->first();
        if ($connection) {
            return ['connection' => $connection, 'is_app_subdomain' => false];
        }

        // Then check app_subdomain
        $connection = self::where('app_subdomain', $subdomain)->first();
        if ($connection) {
            return ['connection' => $connection, 'is_app_subdomain' => true];
        }

        return ['connection' => null, 'is_app_subdomain' => false];
    }

    /**
     * Get the full proxy URL for this connection (requires login).
     */
    public function getProxyUrl(): string
    {
        return $this->buildProxyUrl($this->subdomain);
    }

    /**
     * Get the app proxy URL for this connection (no login required).
     * Uses the long app_subdomain for security.
     */
    public function getAppProxyUrl(): string
    {
        return $this->buildProxyUrl($this->app_subdomain);
    }

    /**
     * Build a proxy URL with the given subdomain.
     */
    private function buildProxyUrl(string $subdomain): string
    {
        $scheme = config('app.proxy_secure') ? 'https' : 'http';
        $domain = $subdomain.'.'.config('app.proxy_domain');
        $port = config('app.proxy_port');

        if ($port && $port !== 80 && $port !== 443) {
            return "{$scheme}://{$domain}:{$port}";
        }

        return "{$scheme}://{$domain}";
    }

    public static function generateSubdomain(): string
    {
        do {
            // 8 characters = 36^8 ≈ 2.8 * 10^12 combinations
            $subdomain = Str::lower(Str::random(8));
            // Ensure no collision with app_subdomains
        } while (self::where('subdomain', $subdomain)->orWhere('app_subdomain', $subdomain)->exists());

        return $subdomain;
    }

    /**
     * Generate a unique 32-character app subdomain for mobile app access.
     */
    public static function generateAppSubdomain(): string
    {
        do {
            // 32 characters = 36^32 ≈ 6.3 * 10^49 combinations (impossible to brute force)
            $subdomain = Str::lower(Str::random(32));
            // Ensure no collision with regular subdomains or other app_subdomains
        } while (self::where('subdomain', $subdomain)->orWhere('app_subdomain', $subdomain)->exists());

        return $subdomain;
    }

    public static function generateConnectionToken(): string
    {
        return Str::random(64);
    }

    /**
     * Get total bytes transferred (in + out).
     */
    public function getTotalBytes(): int
    {
        return $this->bytes_in + $this->bytes_out;
    }

    /**
     * Format bytes as human-readable string (KB, MB, GB).
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Get formatted bytes in.
     */
    public function getFormattedBytesIn(): string
    {
        return self::formatBytes($this->bytes_in);
    }

    /**
     * Get formatted bytes out.
     */
    public function getFormattedBytesOut(): string
    {
        return self::formatBytes($this->bytes_out);
    }

    /**
     * Get formatted total bytes.
     */
    public function getFormattedTotalBytes(): string
    {
        return self::formatBytes($this->getTotalBytes());
    }
}
