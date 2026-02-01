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
        'connection_token',
        'app_token',
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
     * Get the full proxy URL for this connection.
     */
    public function getProxyUrl(): string
    {
        $scheme = config('app.proxy_secure') ? 'https' : 'http';
        $domain = $this->subdomain.'.'.config('app.proxy_domain');
        $port = config('app.proxy_port');

        if ($port && $port !== 80 && $port !== 443) {
            return "{$scheme}://{$domain}:{$port}";
        }

        return "{$scheme}://{$domain}";
    }

    public static function generateSubdomain(): string
    {
        do {
            // 16 characters = 36^16 ≈ 7.9 * 10^24 combinations (virtually impossible to brute force)
            $subdomain = Str::lower(Str::random(16));
        } while (self::where('subdomain', $subdomain)->exists());

        return $subdomain;
    }

    public static function generateConnectionToken(): string
    {
        return Str::random(64);
    }

    /**
     * Generate a new app token for mobile app authentication.
     */
    public static function generateAppToken(): string
    {
        return Str::random(64);
    }

    /**
     * Get the mobile app URL with authentication token.
     */
    public function getAppUrl(string $plainToken): string
    {
        return $this->getProxyUrl().'?app_token='.$plainToken;
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
