<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropLegacyColumns('articles', [
            'title',
            'subtitle',
            'slug',
            'content',
            'excerpt',
            'locale',
            'seo_title',
            'seo_description',
        ], 'articles_slug_unique');

        $this->dropLegacyColumns('news', [
            'title',
            'subtitle',
            'slug',
            'content',
            'excerpt',
            'locale',
            'seo_title',
            'seo_description',
        ], 'news_slug_unique');

        $this->dropLegacyColumns('course_categories', [
            'name',
            'slug',
        ], 'course_categories_slug_unique');
    }

    public function down(): void
    {
        // Non-reversible cleanup; translated columns remain the source of truth.
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropLegacyColumns(string $table, array $columns, string $slugUniqueIndex): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $legacy = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));

        if ($legacy !== []) {
            Schema::table($table, function (Blueprint $blueprint) use ($legacy) {
                $blueprint->dropColumn($legacy);
            });
        }

        // SQLite can leave the old unique index behind after a partial column drop.
        if (! Schema::hasColumn($table, 'slug')) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($slugUniqueIndex) {
                    $blueprint->dropUnique($slugUniqueIndex);
                });
            } catch (\Throwable) {
                // Index already gone — nothing to do.
            }
        }
    }
};
