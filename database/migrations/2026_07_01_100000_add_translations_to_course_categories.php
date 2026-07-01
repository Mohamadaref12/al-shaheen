<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');

            $table->unique(['course_category_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        foreach (DB::table('course_categories')->get() as $category) {
            foreach (['en', 'ar'] as $locale) {
                DB::table('course_category_translations')->insert([
                    'course_category_id' => $category->id,
                    'locale'               => $locale,
                    'name'                 => $category->name,
                    'slug'                 => $category->slug,
                ]);
            }
        }

        Schema::table('course_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('course_categories', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->unique()->after('name');
        });

        foreach (DB::table('course_category_translations')->orderBy('id')->get() as $translation) {
            if ($translation->locale !== 'en') {
                continue;
            }

            DB::table('course_categories')->where('id', $translation->course_category_id)->update([
                'name' => $translation->name,
                'slug' => $translation->slug,
            ]);
        }

        Schema::dropIfExists('course_category_translations');
    }
};
