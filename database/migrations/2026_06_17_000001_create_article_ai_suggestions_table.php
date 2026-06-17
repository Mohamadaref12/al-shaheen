<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('focus', 50)->default('all');
            $table->string('locale', 5)->default('ar');
            $table->json('original_snapshot');
            $table->json('suggestions');
            $table->json('notes')->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->enum('status', ['completed', 'failed'])->default('completed');
            $table->timestamps();

            $table->index(['article_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_ai_suggestions');
    }
};
