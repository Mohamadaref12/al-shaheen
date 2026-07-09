<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $main = [
            [
                'slug'            => 'news',
                'name_en'         => 'News',
                'name_ar'         => 'أخبار',
                'description_en'  => 'Latest news and current affairs coverage.',
                'description_ar'  => 'آخر الأخبار وتغطية الشؤون الجارية.',
            ],
            [
                'slug'            => 'reports',
                'name_en'         => 'Reports',
                'name_ar'         => 'تقارير',
                'description_en'  => 'In-depth reports, investigations, and analysis.',
                'description_ar'  => 'تقارير معمّقة وتحقيقات وتحليلات.',
            ],
            [
                'slug'            => 'interviews',
                'name_en'         => 'Interviews',
                'name_ar'         => 'حوارات',
                'description_en'  => 'Exclusive interviews and guest conversations.',
                'description_ar'  => 'حوارات حصرية ولقاءات مع ضيوف مميزين.',
            ],
            [
                'slug'            => 'opinion',
                'name_en'         => 'Opinion',
                'name_ar'         => 'رأي',
                'description_en'  => 'Opinion pieces, commentary, and editorials.',
                'description_ar'  => 'مقالات رأي وتعليقات وافتتاحيات.',
            ],
            [
                'slug'            => 'multimedia',
                'name_en'         => 'Multimedia',
                'name_ar'         => 'وسائط متعددة',
                'description_en'  => 'Video, audio, and visual storytelling.',
                'description_ar'  => 'فيديو وصوت ومحتوى بصري.',
            ],
            [
                'slug'            => 'training',
                'name_en'         => 'Training',
                'name_ar'         => 'تدريب',
                'description_en'  => 'Journalism training resources and workshops.',
                'description_ar'  => 'موارد تدريب صحفي وورش عمل.',
            ],
        ];

        $sub = [
            'news' => [
                ['slug' => 'local', 'name_en' => 'Local', 'name_ar' => 'محلي', 'description_en' => 'Local and domestic news.', 'description_ar' => 'أخبار محلية ووطنية.'],
                ['slug' => 'arab', 'name_en' => 'Arab World', 'name_ar' => 'العالم العربي', 'description_en' => 'News from across the Arab region.', 'description_ar' => 'أخبار من أنحاء العالم العربي.'],
                ['slug' => 'international', 'name_en' => 'International', 'name_ar' => 'دولي', 'description_en' => 'Global news and world affairs.', 'description_ar' => 'أخبار عالمية وشؤون دولية.'],
                ['slug' => 'economy', 'name_en' => 'Economy', 'name_ar' => 'اقتصاد', 'description_en' => 'Business, markets, and economic policy.', 'description_ar' => 'أعمال وأسواق وسياسات اقتصادية.'],
                ['slug' => 'sports', 'name_en' => 'Sports', 'name_ar' => 'رياضة', 'description_en' => 'Sports news and coverage.', 'description_ar' => 'أخبار رياضية وتغطيات.'],
                ['slug' => 'tech', 'name_en' => 'Technology', 'name_ar' => 'تكنولوجيا', 'description_en' => 'Technology, innovation, and digital trends.', 'description_ar' => 'تكنولوجيا وابتكار واتجاهات رقمية.'],
            ],
            'reports' => [
                ['slug' => 'investigations', 'name_en' => 'Investigations', 'name_ar' => 'تحقيقات', 'description_en' => 'Investigative journalism and deep dives.', 'description_ar' => 'صحافة استقصائية وتحقيقات معمّقة.'],
                ['slug' => 'analysis', 'name_en' => 'Analysis', 'name_ar' => 'تحليل', 'description_en' => 'Expert analysis and contextual reporting.', 'description_ar' => 'تحليلات خبيرة وتقارير سياقية.'],
                ['slug' => 'statistics', 'name_en' => 'Statistics', 'name_ar' => 'إحصاءات', 'description_en' => 'Data-driven stories and statistics.', 'description_ar' => 'قصص مبنية على البيانات وإحصاءات.'],
            ],
            'interviews' => [
                ['slug' => 'profiles', 'name_en' => 'Profiles', 'name_ar' => 'ملفات', 'description_en' => 'Profile interviews with public figures.', 'description_ar' => 'حوارات ملفات مع شخصيات عامة.'],
                ['slug' => 'exclusive', 'name_en' => 'Exclusive', 'name_ar' => 'حصرية', 'description_en' => 'Exclusive and one-on-one interviews.', 'description_ar' => 'حوارات حصرية ولقاءات خاصة.'],
            ],
            'opinion' => [
                ['slug' => 'opinion-articles', 'name_en' => 'Opinion Articles', 'name_ar' => 'مقالات رأي', 'description_en' => 'Long-form opinion articles.', 'description_ar' => 'مقالات رأي مطوّلة.'],
                ['slug' => 'commentary', 'name_en' => 'Commentary', 'name_ar' => 'تعليق', 'description_en' => 'Short commentary and reactions.', 'description_ar' => 'تعليقات قصيرة وردود فعل.'],
            ],
            'multimedia' => [
                ['slug' => 'video', 'name_en' => 'Video', 'name_ar' => 'فيديو', 'description_en' => 'Video reports and documentaries.', 'description_ar' => 'تقارير وثائقية وفيديو.'],
                ['slug' => 'podcast', 'name_en' => 'Podcast', 'name_ar' => 'بودكاست', 'description_en' => 'Podcast episodes and audio features.', 'description_ar' => 'حلقات بودكاست ومحتوى صوتي.'],
                ['slug' => 'infographic', 'name_en' => 'Infographic', 'name_ar' => 'إنفوغراف', 'description_en' => 'Visual infographics and data stories.', 'description_ar' => 'إنفوغرافيك وقصص بيانات بصرية.'],
            ],
            'training' => [
                ['slug' => 'workshops', 'name_en' => 'Workshops', 'name_ar' => 'ورش عمل', 'description_en' => 'Hands-on journalism workshops.', 'description_ar' => 'ورش عمل صحفية تطبيقية.'],
                ['slug' => 'courses', 'name_en' => 'Courses', 'name_ar' => 'دورات', 'description_en' => 'Structured training courses.', 'description_ar' => 'دورات تدريبية منظمة.'],
            ],
        ];

        foreach ($main as $i => $cat) {
            $parent = $this->upsertCategory($cat, $i + 1);

            foreach ($sub[$cat['slug']] ?? [] as $j => $child) {
                $this->upsertCategory($child, $j + 1, $parent->id);
            }
        }
    }

    private function upsertCategory(array $data, int $sortOrder, ?int $parentId = null): Category
    {
        $slug = $data['slug'];

        $category = Category::query()
            ->when(
                $parentId,
                fn ($query) => $query->where('parent_id', $parentId),
                fn ($query) => $query->whereNull('parent_id')
            )
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug)->where('locale', 'en'))
            ->first() ?? new Category();

        $category->fill([
            'parent_id'  => $parentId,
            'sort_order' => $sortOrder,
            'is_active'  => true,
        ])->save();

        foreach (['en', 'ar'] as $locale) {
            $category->translateOrNew($locale)->fill([
                'name'        => $data["name_{$locale}"],
                'slug'        => $slug,
                'description' => $data["description_{$locale}"] ?? null,
            ]);
        }

        $category->save();

        return $category;
    }
}
