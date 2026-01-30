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
        'status',
        'last_connected_at',
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

    public static function generateSubdomain(): string
    {
        do {
            $subdomain = Str::lower(Str::random(8));
        } while (self::where('subdomain', $subdomain)->exists());

        return $subdomain;
    }

    public static function generateConnectionToken(): string
    {
        return Str::random(64);
    }
}
