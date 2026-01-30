<?php

namespace App\Events;

use App\Models\HaConnection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TunnelDisconnected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HaConnection $connection
    ) {}
}
