<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateStatusEnum(['draft', 'under_review', 'rejected', 'published', 'archived']);
    }

    public function down(): void
    {
        DB::table('news')->where('status', 'rejected')->update(['status' => 'draft']);

        $this->updateStatusEnum(['draft', 'under_review', 'published', 'archived']);
    }

    /**
     * @param  list<string>  $allowed
     */
    private function updateStatusEnum(array $allowed): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $enumList = collect($allowed)->map(fn (string $value) => "'{$value}'")->implode(', ');

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE news MODIFY COLUMN status ENUM({$enumList}) NOT NULL DEFAULT 'draft'");

            return;
        }

        // SQLite cannot MODIFY columns; Schema::change() rebuilds the table.
        // Drop/re-add the unique index so Laravel does not recreate it with empty columns.
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('news', function (Blueprint $table) use ($allowed) {
            $table->enum('status', $allowed)->default('draft')->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};
