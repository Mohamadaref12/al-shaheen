<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_editor_pick')->default(false)->after('is_premium');
            $table->unsignedSmallInteger('editor_pick_order')->nullable()->after('is_editor_pick');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['is_editor_pick', 'editor_pick_order']);
        });
    }
};
