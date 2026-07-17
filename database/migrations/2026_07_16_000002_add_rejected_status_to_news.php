<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE news MODIFY COLUMN status ENUM('draft', 'under_review', 'rejected', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE news SET status = 'draft' WHERE status = 'rejected'");
        DB::statement("ALTER TABLE news MODIFY COLUMN status ENUM('draft', 'under_review', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }
};
