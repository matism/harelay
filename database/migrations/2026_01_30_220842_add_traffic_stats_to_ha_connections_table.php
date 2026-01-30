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
            // Track bytes transferred (use unsigned bigint for large values)
            $table->unsignedBigInteger('bytes_in')->default(0)->after('last_connected_at');
            $table->unsignedBigInteger('bytes_out')->default(0)->after('bytes_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ha_connections', function (Blueprint $table) {
            $table->dropColumn(['bytes_in', 'bytes_out']);
        });
    }
};
