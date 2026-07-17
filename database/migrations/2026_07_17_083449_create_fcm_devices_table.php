<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 32)->default('web'); // web, dashboard, android, ios
            $table->string('locale', 8)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_devices');
    }
};
