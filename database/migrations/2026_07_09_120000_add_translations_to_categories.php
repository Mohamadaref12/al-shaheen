<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function arabicNames(): array
    {
        return [
            'news'               => 'أخبار',
            'reports'            => 'تقارير',
            'interviews'         => 'حوارات',
            'opinion'            => 'رأي',
            'multimedia'         => 'وسائط متعددة',
            'training'           => 'تدريب',
            'local'              => 'محلي',
            'arab'               => 'العالم العربي',
            'international'      => 'دولي',
            'economy'            => 'اقتصاد',
            'sports'             => 'رياضة',
            'tech'               => 'تكنولوجيا',
            'investigations'     => 'تحقيقات',
            'analysis'           => 'تحليل',
            'statistics'         => 'إحصاءات',
            'opinion-articles'   => 'مقالات رأي',
            'commentary'         => 'تعليق',
            'video'              => 'فيديو',
            'podcast'            => 'بودكاست',
            'infographic'        => 'إنفوغراف',
            'profiles'           => 'ملفات',
            'exclusive'          => 'حصرية',
            'workshops'          => 'ورش عمل',
            'courses'            => 'دورات',
        ];
    }

    public function up(): void
    {
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->unique(['category_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        $arabicNames = $this->arabicNames();

        foreach (DB::table('categories')->get() as $category) {
            DB::table('category_translations')->insert([
                'category_id' => $category->id,
                'locale'      => 'en',
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
            ]);

            DB::table('category_translations')->insert([
                'category_id' => $category->id,
                'locale'      => 'ar',
                'name'        => $arabicNames[$category->slug] ?? $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->after('parent_id');
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
        });

        foreach (DB::table('category_translations')->orderBy('id')->get() as $translation) {
            if ($translation->locale !== 'en') {
                continue;
            }

            DB::table('categories')->where('id', $translation->category_id)->update([
                'name'        => $translation->name,
                'slug'        => $translation->slug,
                'description' => $translation->description,
            ]);
        }

        Schema::dropIfExists('category_translations');
    }
};
