<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_courses', function (Blueprint $table) {
            $table->foreignId('course_category_id')
                ->nullable()
                ->after('description')
                ->constrained('course_categories')
                ->nullOnDelete();

            $table->text('excerpt')->nullable()->after('course_category_id');
            $table->longText('about_content')->nullable()->after('excerpt');
            $table->string('about_image')->nullable()->after('about_content');
            $table->decimal('price', 10, 2)->nullable()->after('about_image');
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
            $table->string('currency', 3)->default('USD')->after('original_price');
            $table->string('instructor_name')->nullable()->after('currency');
            $table->string('instructor_avatar')->nullable()->after('instructor_name');
            $table->string('instructor_label')->nullable()->after('instructor_avatar');
            $table->unsignedSmallInteger('duration_weeks')->nullable()->after('instructor_label');
            $table->unsignedSmallInteger('downloadable_files_count')->default(0)->after('duration_weeks');
            $table->boolean('has_lifetime_access')->default(true)->after('downloadable_files_count');
            $table->json('learning_outcomes')->nullable()->after('has_lifetime_access');
            $table->decimal('rating', 2, 1)->nullable()->after('learning_outcomes');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating');
            $table->string('video_preview_url')->nullable()->after('reviews_count');
        });

        $this->migrateLegacyCategories();

        Schema::table('training_courses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('training_courses', function (Blueprint $table) {
            $table->string('category')->after('description');
        });

        $courses = DB::table('training_courses')
            ->leftJoin('course_categories', 'training_courses.course_category_id', '=', 'course_categories.id')
            ->select('training_courses.id', 'course_categories.name')
            ->get();

        foreach ($courses as $course) {
            DB::table('training_courses')
                ->where('id', $course->id)
                ->update(['category' => $course->name ?? 'uncategorized']);
        }

        Schema::table('training_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_category_id');
            $table->dropColumn([
                'excerpt',
                'about_content',
                'about_image',
                'price',
                'original_price',
                'currency',
                'instructor_name',
                'instructor_avatar',
                'instructor_label',
                'duration_weeks',
                'downloadable_files_count',
                'has_lifetime_access',
                'learning_outcomes',
                'rating',
                'reviews_count',
                'video_preview_url',
            ]);
        });
    }

    private function migrateLegacyCategories(): void
    {
        $legacyCategories = DB::table('training_courses')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $slugMap = [];

        foreach ($legacyCategories as $name) {
            $slug = Str::slug($name) ?: 'category-' . Str::random(6);

            $categoryId = DB::table('course_categories')->insertGetId([
                'name'       => $name,
                'slug'       => $slug,
                'icon'       => null,
                'sort_order' => 0,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $slugMap[$name] = $categoryId;
        }

        foreach ($slugMap as $name => $categoryId) {
            DB::table('training_courses')
                ->where('category', $name)
                ->update(['course_category_id' => $categoryId]);
        }
    }
};
