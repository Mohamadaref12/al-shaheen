<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $writers    = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $primaries  = Category::whereNull('parent_id')->pluck('id')->toArray();
        $secondaries= Category::whereNotNull('parent_id')->pluck('id')->toArray();
        $tagIds     = Tag::pluck('id')->toArray();
        $readers    = User::whereHas('reader')->pluck('id')->toArray();

        $statuses = ['published', 'published', 'published', 'draft', 'under_review', 'rejected'];

        for ($i = 0; $i < 30; $i++) {
            $title  = fake('ar_SA')->sentence(rand(5, 10));
            $status = fake()->randomElement($statuses);

            $article = Article::create([
                'author_id'          => fake()->randomElement($writers),
                'primary_category_id'=> fake()->randomElement($primaries),
                'title'              => $title,
                'subtitle'           => fake('ar_SA')->sentence(6),
                'slug'               => Str::slug($title) ?: 'article-' . ($i + 1),
                'content'            => implode("\n\n", fake('ar_SA')->paragraphs(rand(5, 10))),
                'excerpt'            => fake('ar_SA')->paragraph(2),
                'featured_image'     => null,
                'locale'             => fake()->randomElement(['ar', 'en']),
                'read_time'          => rand(3, 15),
                'is_breaking'        => fake()->boolean(10),
                'status'             => $status,
                'views_count'        => $status === 'published' ? rand(50, 5000) : 0,
                'published_at'       => $status === 'published' ? now()->subDays(rand(1, 60)) : null,
            ]);

            // secondary categories (0-2)
            if (count($secondaries)) {
                $article->secondaryCategories()->attach(
                    fake()->randomElements($secondaries, rand(0, 2))
                );
            }

            // tags (1-4)
            $article->tags()->attach(
                fake()->randomElements($tagIds, rand(1, 4))
            );

            // comments on published articles
            if ($status === 'published' && count($readers)) {
                $commentCount = rand(0, 8);
                for ($c = 0; $c < $commentCount; $c++) {
                    Comment::create([
                        'user_id'    => fake()->randomElement($readers),
                        'article_id' => $article->id,
                        'body'       => fake('ar_SA')->paragraph(),
                        'status'     => fake()->randomElement(['approved', 'approved', 'pending', 'rejected']),
                    ]);
                }
            }
        }
    }
}
