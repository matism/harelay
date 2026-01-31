<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceLinkController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;

// Marketing routes
Route::get('/', [MarketingController::class, 'home'])->name('marketing.home');
Route::get('/how-it-works', [MarketingController::class, 'howItWorks'])->name('marketing.how-it-works');
Route::get('/privacy', [MarketingController::class, 'privacy'])->name('marketing.privacy');
Route::get('/imprint', [MarketingController::class, 'imprint'])->name('marketing.imprint');
Route::get('/vs/nabu-casa', [MarketingController::class, 'vsNabuCasa'])->name('marketing.vs-nabu-casa');
Route::get('/vs/homeflow', [MarketingController::class, 'vsHomeflow'])->name('marketing.vs-homeflow');

// Device linking
Route::get('/link', [DeviceLinkController::class, 'show'])->name('device.link');
Route::post('/link', [DeviceLinkController::class, 'link'])
    ->middleware(['auth', 'verified', 'throttle:10,1']); // 10 per minute

// Dashboard routes (authenticated)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/setup', [DashboardController::class, 'setup'])->name('dashboard.setup');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');

    // Connection management (rate limited to prevent abuse)
    Route::post('/connection', [ConnectionController::class, 'store'])
        ->middleware('throttle:5,1') // 5 per minute
        ->name('connection.store');
    Route::post('/connection/regenerate-token', [ConnectionController::class, 'regenerateToken'])
        ->middleware('throttle:5,1') // 5 per minute
        ->name('connection.regenerate-token');
    Route::patch('/connection/subdomain', [ConnectionController::class, 'updateSubdomain'])
        ->middleware('throttle:5,1') // 5 per minute
        ->name('connection.update-subdomain');
    Route::delete('/connection', [ConnectionController::class, 'destroy'])
        ->middleware('throttle:5,1') // 5 per minute
        ->name('connection.destroy');

    // Profile (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Subdomain proxy routes (for production with nginx/apache)
// Note: In local development, SubdomainProxy middleware handles this instead
// because php artisan serve doesn't support Route::domain() properly
Route::domain('{subdomain}.'.config('app.proxy_domain', 'harelay.com'))
    ->middleware(['web', 'proxy.security'])
    ->group(function () {
        Route::any('/{path?}', [ProxyController::class, 'handle'])
            ->where('path', '.*')
            ->name('proxy.handle');
    });

require __DIR__.'/auth.php';
