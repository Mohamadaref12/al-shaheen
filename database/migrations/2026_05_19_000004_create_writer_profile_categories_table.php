<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributor_categories', function (Blueprint $table) {
            $table->foreignId('contributor_id')->constrained('writer')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['contributor_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_categories');
    }
};
