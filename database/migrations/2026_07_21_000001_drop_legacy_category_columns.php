<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Older installs (especially SQLite) may still have name/slug/description on
        // categories after translations were introduced. Leftover unique slug values
        // then break seeders that only write translated slugs.
        $legacy = array_values(array_filter(
            ['name', 'slug', 'description'],
            fn (string $column) => Schema::hasColumn('categories', $column)
        ));

        if ($legacy !== []) {
            Schema::table('categories', function (Blueprint $table) use ($legacy) {
                $table->dropColumn($legacy);
            });
        }

        // Drop orphan unique index if the column was already removed but the index remained.
        if (! Schema::hasColumn('categories', 'slug')) {
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->dropUnique('categories_slug_unique');
                });
            } catch (\Throwable) {
                // Index already gone — nothing to do.
            }
        }
    }

    public function down(): void
    {
        // Non-reversible cleanup; translated columns remain the source of truth.
    }
};
