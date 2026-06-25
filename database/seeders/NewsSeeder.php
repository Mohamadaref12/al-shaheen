<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();

        if ($authors === [] || $categories === []) {
            return;
        }

        $statuses = ['published', 'published', 'published', 'draft', 'under_review'];

        for ($i = 0; $i < 15; $i++) {
            $title = fake()->sentence(rand(4, 8));
            $status = fake()->randomElement($statuses);

            News::create([
                'author_id' => fake()->randomElement($authors),
                'category_id' => fake()->randomElement($categories),
                'title' => $title,
                'subtitle' => fake()->optional(0.6)->sentence(),
                'slug' => Str::slug($title) ?: 'news-'.($i + 1),
                'excerpt' => fake()->paragraph(2),
                'content' => implode("\n\n", fake()->paragraphs(rand(4, 10))),
                'locale' => fake()->randomElement(['ar', 'ar', 'en']),
                'read_time' => rand(3, 12),
                'is_breaking' => $status === 'published' && fake()->boolean(15),
                'is_premium' => fake()->boolean(20),
                'status' => $status,
                'views_count' => $status === 'published' ? rand(50, 12000) : 0,
                'published_at' => $status === 'published' ? now()->subDays(rand(1, 60)) : null,
            ]);
        }
    }
}
