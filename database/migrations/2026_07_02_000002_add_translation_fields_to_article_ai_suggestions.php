<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_ai_suggestions', function (Blueprint $table) {
            $table->string('kind', 20)->default('improvement')->after('focus');
            $table->string('source_locale', 5)->nullable()->after('locale');
            $table->string('target_locale', 5)->nullable()->after('source_locale');
        });
    }

    public function down(): void
    {
        Schema::table('article_ai_suggestions', function (Blueprint $table) {
            $table->dropColumn(['kind', 'source_locale', 'target_locale']);
        });
    }
};
