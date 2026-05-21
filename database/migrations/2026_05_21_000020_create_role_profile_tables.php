<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Readers – no extra fields beyond users
        Schema::create('readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Contributors – bio, photo, portfolio
        Schema::create('contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('portfolio_link')->nullable();
            $table->timestamps();
        });

        // Contributor → Category pivot
        Schema::create('contributor_profile_categories', function (Blueprint $table) {
            $table->foreignId('contributor_id')->constrained('contributors')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['contributor_id', 'category_id']);
        });

        // Editors – no extra fields beyond users
        Schema::create('editors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Admins – no extra fields beyond users
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_profile_categories');
        Schema::dropIfExists('contributors');
        Schema::dropIfExists('readers');
        Schema::dropIfExists('editors');
        Schema::dropIfExists('admins');
    }
};
