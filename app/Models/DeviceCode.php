<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DeviceCode extends Model
{
    protected $fillable = [
        'device_code',
        'user_code',
        'user_id',
        'subdomain',
        'connection_token',
        'status',
        'device_name',
        'expires_at',
        'linked_at',
    ];

    protected $hidden = ['connection_token'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'linked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public static function generateUserCode(): string
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4));
        } while (self::where('user_code', $code)->where('status', 'pending')->exists());

        return $code;
    }

    public static function generateDeviceCode(): string
    {
        return Str::random(64);
    }
}
