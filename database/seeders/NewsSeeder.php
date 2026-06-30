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
            $titleEn = fake()->sentence(rand(4, 8));
            $titleAr = 'خبر تجريبي ' . ($i + 1);
            $status = fake()->randomElement($statuses);

            $news = News::create([
                'author_id'   => fake()->randomElement($authors),
                'category_id' => fake()->randomElement($categories),
                'read_time'   => rand(3, 12),
                'is_breaking' => $status === 'published' && fake()->boolean(15),
                'is_premium'  => fake()->boolean(20),
                'status'      => $status,
                'views_count' => $status === 'published' ? rand(50, 12000) : 0,
                'published_at'=> $status === 'published' ? now()->subDays(rand(1, 60)) : null,
            ]);

            $news->title_en = $titleEn;
            $news->subtitle_en = fake()->optional(0.6)->sentence();
            $news->slug_en = Str::slug($titleEn) ?: 'news-en-' . ($i + 1);
            $news->excerpt_en = fake()->paragraph(2);
            $news->content_en = implode("\n\n", fake()->paragraphs(rand(4, 10)));

            $news->title_ar = $titleAr;
            $news->subtitle_ar = 'عنوان فرعي للخبر';
            $news->slug_ar = Str::slug($titleAr) ?: 'news-ar-' . ($i + 1);
            $news->excerpt_ar = 'ملخص الخبر بالعربية.';
            $news->content_ar = 'محتوى الخبر باللغة العربية. ' . fake()->paragraph(4);
            $news->save();
        }
    }
}
