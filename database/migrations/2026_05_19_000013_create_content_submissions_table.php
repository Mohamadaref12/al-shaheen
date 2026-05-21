<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('writer_id')->constrained('writer')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('content');
            $table->enum('type', ['article', 'report', 'interview', 'media'])->default('article');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'ready', 'approved', 'rejected'])->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_submissions');
    }
};
