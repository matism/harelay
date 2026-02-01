<?php

use App\Models\HaConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ha_connections', function (Blueprint $table) {
            // App subdomain for mobile app access (no login required)
            // 32 chars lowercase alphanumeric = 36^32 combinations (impossible to brute force)
            $table->string('app_subdomain', 32)->nullable()->unique()->after('subdomain');
            $table->index('app_subdomain');
        });

        // Generate app_subdomain for existing connections
        HaConnection::whereNull('app_subdomain')->each(function (HaConnection $connection) {
            $connection->update([
                'app_subdomain' => $this->generateUniqueAppSubdomain(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ha_connections', function (Blueprint $table) {
            $table->dropIndex(['app_subdomain']);
            $table->dropColumn('app_subdomain');
        });
    }

    /**
     * Generate a unique 32-character app subdomain.
     */
    private function generateUniqueAppSubdomain(): string
    {
        do {
            $subdomain = strtolower(Str::random(32));
        } while (HaConnection::where('app_subdomain', $subdomain)->exists());

        return $subdomain;
    }
};
