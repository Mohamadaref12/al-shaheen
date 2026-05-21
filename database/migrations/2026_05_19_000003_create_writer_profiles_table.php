<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('portfolio_link')->nullable();
            $table->string('experience_level')->nullable();
            $table->json('languages')->nullable();
            $table->json('editorial_specialties')->nullable();
            $table->string('location')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_verified_writer')->default(false);
            $table->string('id_verification_file')->nullable();
            $table->string('media_affiliation')->nullable();
            $table->json('sample_publications')->nullable();
            $table->enum('application_status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'suspended'])->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writer');
    }
};
