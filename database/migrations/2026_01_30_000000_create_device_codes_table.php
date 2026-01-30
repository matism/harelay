<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_codes', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 64)->unique();
            $table->string('user_code', 10)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('subdomain')->nullable();
            $table->string('connection_token')->nullable();
            $table->enum('status', ['pending', 'linked', 'expired', 'used'])->default('pending');
            $table->string('device_name')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();

            $table->index('device_code');
            $table->index('user_code');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_codes');
    }
};
