<?php

namespace Database\Factories;

use App\Models\HaConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HaConnection>
 */
class HaConnectionFactory extends Factory
{
    protected $model = HaConnection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subdomain' => $this->faker->unique()->regexify('[a-z0-9]{8}'),
            'connection_token' => HaConnection::generateConnectionToken(),
            'status' => 'disconnected',
            'last_connected_at' => null,
        ];
    }

    public function connected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'connected',
            'last_connected_at' => now(),
        ]);
    }
}
