<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan' => 'free',
            'status' => 'active',
            'trial_ends_at' => null,
            'expires_at' => null,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'monthly',
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'annual',
            'expires_at' => now()->addYear(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }
}
