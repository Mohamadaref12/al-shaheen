<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opinions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->unsignedInteger('read_time')->default(5);
            $table->boolean('is_premium')->default(false);
            $table->enum('status', ['draft', 'under_review', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('opinion_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opinion_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->unique(['opinion_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opinion_translations');
        Schema::dropIfExists('opinions');
    }
};
