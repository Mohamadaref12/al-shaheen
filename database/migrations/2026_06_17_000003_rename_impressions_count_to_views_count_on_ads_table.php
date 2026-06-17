<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ads', 'impressions_count')) {
            return;
        }

        Schema::table('ads', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('is_active');
        });

        DB::table('ads')->update([
            'views_count' => DB::raw('impressions_count'),
        ]);

        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('impressions_count');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('ads', 'views_count')) {
            return;
        }

        Schema::table('ads', function (Blueprint $table) {
            $table->unsignedBigInteger('impressions_count')->default(0)->after('is_active');
        });

        DB::table('ads')->update([
            'impressions_count' => DB::raw('views_count'),
        ]);

        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });
    }
};
