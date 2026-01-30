<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $plan = 'free'): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $subscription = $user->subscription;

        // During beta, everyone gets free access
        if (! $subscription) {
            return $next($request);
        }

        // Check if subscription is active
        if (! $subscription->isActive()) {
            return redirect()->route('dashboard.subscription')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // Check if subscription meets the required plan level
        if ($plan !== 'free' && ! $this->meetsRequirement($subscription->plan, $plan)) {
            return redirect()->route('dashboard.subscription')
                ->with('error', "This feature requires a {$plan} subscription.");
        }

        return $next($request);
    }

    private function meetsRequirement(string $currentPlan, string $requiredPlan): bool
    {
        $planLevels = [
            'free' => 0,
            'monthly' => 1,
            'annual' => 2,
        ];

        $currentLevel = $planLevels[$currentPlan] ?? 0;
        $requiredLevel = $planLevels[$requiredPlan] ?? 0;

        return $currentLevel >= $requiredLevel;
    }
}
