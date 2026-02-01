<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ha_connections', function (Blueprint $table) {
            // App token for mobile app authentication (stored hashed like connection_token)
            $table->string('app_token')->nullable()->after('connection_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ha_connections', function (Blueprint $table) {
            $table->dropColumn('app_token');
        });
    }
};
