<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->enum('status', ['draft', 'under_review', 'rejected', 'published', 'archived'])
                ->default('draft')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('news')->where('status', 'rejected')->update(['status' => 'draft']);

        Schema::table('news', function (Blueprint $table) {
            $table->enum('status', ['draft', 'under_review', 'published', 'archived'])
                ->default('draft')
                ->change();
        });
    }
};
