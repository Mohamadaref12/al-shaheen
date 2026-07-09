<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Opinion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OpinionSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();

        $categories = Category::query()
            ->whereHas('parent', fn ($q) => $q->whereTranslation('slug', 'opinion'))
            ->pluck('id')
            ->toArray();

        if ($authors === []) {
            return;
        }

        $headlines = [
            ['en' => 'The Future of Regional Journalism', 'ar' => 'مستقبل الصحافة الإقليمية'],
            ['en' => 'Why Independent Media Matters Now', 'ar' => 'لماذا الإعلام المستقل مهم الآن'],
            ['en' => 'Opinion: Reform Starts with Transparency', 'ar' => 'رأي: الإصلاح يبدأ بالشفافية'],
            ['en' => 'Youth Voices in the Arab World', 'ar' => 'أصوات الشباب في العالم العربي'],
            ['en' => 'Economic Policy Beyond Headlines', 'ar' => 'السياسة الاقتصادية بعيداً عن العناوين'],
            ['en' => 'Culture as a Bridge, Not a Barrier', 'ar' => 'الثقافة جسر لا حاجز'],
        ];

        foreach ($headlines as $i => $headline) {
            $status = $i < 5 ? 'published' : 'draft';

            $opinion = Opinion::create([
                'author_id'    => fake()->randomElement($authors),
                'category_id'  => $categories !== [] ? fake()->randomElement($categories) : null,
                'read_time'    => rand(3, 8),
                'is_premium'   => fake()->boolean(15),
                'status'       => $status,
                'views_count'  => $status === 'published' ? rand(50, 5000) : 0,
                'published_at' => $status === 'published' ? now()->subHours(($i + 1) * 3) : null,
            ]);

            $opinion->title_en = $headline['en'];
            $opinion->slug_en = Str::slug($headline['en']) ?: 'opinion-en-' . ($i + 1);
            $opinion->excerpt_en = fake()->paragraph(2);
            $opinion->content_en = implode("\n\n", fake()->paragraphs(rand(4, 8)));

            $opinion->title_ar = $headline['ar'];
            $opinion->slug_ar = Str::slug($headline['ar']) ?: 'opinion-ar-' . ($i + 1);
            $opinion->excerpt_ar = 'ملخص الرأي باللغة العربية.';
            $opinion->content_ar = 'محتوى الرأي باللغة العربية. ' . fake()->paragraph(4);
            $opinion->save();
        }
    }
}
