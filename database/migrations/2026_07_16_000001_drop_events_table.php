<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('events');
    }

    public function down(): void
    {
        // Events feature removed — no rollback.
    }
};
