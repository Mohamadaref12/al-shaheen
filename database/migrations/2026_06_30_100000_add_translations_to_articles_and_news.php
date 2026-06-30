<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug');
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->unique(['article_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        Schema::create('news_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->unique(['news_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        $this->migrateArticles();
        $this->migrateNews();

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'subtitle',
                'slug',
                'content',
                'excerpt',
                'locale',
                'seo_title',
                'seo_description',
            ]);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'subtitle',
                'slug',
                'content',
                'excerpt',
                'locale',
                'seo_title',
                'seo_description',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title')->after('primary_category_id');
            $table->string('subtitle')->nullable()->after('title');
            $table->string('slug')->unique()->after('subtitle');
            $table->longText('content')->after('slug');
            $table->text('excerpt')->nullable()->after('content');
            $table->enum('locale', ['ar', 'en'])->default('ar')->after('video_embed');
            $table->string('seo_title')->nullable()->after('views_count');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('title')->after('category_id');
            $table->string('subtitle')->nullable()->after('title');
            $table->string('slug')->unique()->after('subtitle');
            $table->longText('content')->nullable()->after('slug');
            $table->text('excerpt')->nullable()->after('content');
            $table->enum('locale', ['ar', 'en'])->default('ar')->after('video_embed');
            $table->string('seo_title')->nullable()->after('views_count');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        foreach (DB::table('article_translations')->orderBy('id')->get() as $translation) {
            DB::table('articles')->where('id', $translation->article_id)->update([
                'title'            => $translation->title,
                'subtitle'         => $translation->subtitle,
                'slug'             => $translation->slug,
                'content'          => $translation->content,
                'excerpt'          => $translation->excerpt,
                'locale'           => $translation->locale,
                'seo_title'        => $translation->seo_title,
                'seo_description'  => $translation->seo_description,
            ]);
        }

        foreach (DB::table('news_translations')->orderBy('id')->get() as $translation) {
            DB::table('news')->where('id', $translation->news_id)->update([
                'title'            => $translation->title,
                'subtitle'         => $translation->subtitle,
                'slug'             => $translation->slug,
                'content'          => $translation->content,
                'excerpt'          => $translation->excerpt,
                'locale'           => $translation->locale,
                'seo_title'        => $translation->seo_title,
                'seo_description'  => $translation->seo_description,
            ]);
        }

        Schema::dropIfExists('news_translations');
        Schema::dropIfExists('article_translations');
    }

    private function migrateArticles(): void
    {
        foreach (DB::table('articles')->get() as $article) {
            DB::table('article_translations')->insert([
                'article_id'       => $article->id,
                'locale'           => $article->locale ?? 'ar',
                'title'            => $article->title,
                'subtitle'         => $article->subtitle,
                'slug'             => $article->slug,
                'content'          => $article->content,
                'excerpt'          => $article->excerpt,
                'seo_title'        => $article->seo_title,
                'seo_description'  => $article->seo_description,
            ]);
        }
    }

    private function migrateNews(): void
    {
        foreach (DB::table('news')->get() as $item) {
            DB::table('news_translations')->insert([
                'news_id'          => $item->id,
                'locale'           => $item->locale ?? 'ar',
                'title'            => $item->title,
                'subtitle'         => $item->subtitle,
                'slug'             => $item->slug,
                'content'          => $item->content,
                'excerpt'          => $item->excerpt,
                'seo_title'        => $item->seo_title,
                'seo_description'  => $item->seo_description,
            ]);
        }
    }
};
