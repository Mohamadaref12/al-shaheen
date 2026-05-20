<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $main = [
            ['name' => 'أخبار',       'slug' => 'news'],
            ['name' => 'تقارير',      'slug' => 'reports'],
            ['name' => 'مقابلات',     'slug' => 'interviews'],
            ['name' => 'رأي',         'slug' => 'opinion'],
            ['name' => 'وسائط متعددة','slug' => 'multimedia'],
            ['name' => 'تدريب',       'slug' => 'training'],
        ];

        $sub = [
            'news' => [
                ['name' => 'محلي',    'slug' => 'local'],
                ['name' => 'عربي',    'slug' => 'arab'],
                ['name' => 'دولي',    'slug' => 'international'],
                ['name' => 'اقتصاد',  'slug' => 'economy'],
                ['name' => 'رياضة',   'slug' => 'sports'],
                ['name' => 'تقنية',   'slug' => 'tech'],
            ],
            'reports' => [
                ['name' => 'تحقيقات', 'slug' => 'investigations'],
                ['name' => 'تحليلات', 'slug' => 'analysis'],
                ['name' => 'إحصاءات', 'slug' => 'statistics'],
            ],
            'opinion' => [
                ['name' => 'مقالات رأي',  'slug' => 'opinion-articles'],
                ['name' => 'تعليقات',     'slug' => 'commentary'],
            ],
            'multimedia' => [
                ['name' => 'فيديو',   'slug' => 'video'],
                ['name' => 'بودكاست', 'slug' => 'podcast'],
                ['name' => 'إنفوغراف','slug' => 'infographic'],
            ],
        ];

        foreach ($main as $i => $cat) {
            $parent = Category::create([
                'name'       => $cat['name'],
                'slug'       => $cat['slug'],
                'sort_order' => $i + 1,
                'is_active'  => true,
            ]);

            foreach ($sub[$cat['slug']] ?? [] as $j => $child) {
                Category::create([
                    'parent_id'  => $parent->id,
                    'name'       => $child['name'],
                    'slug'       => $child['slug'],
                    'sort_order' => $j + 1,
                    'is_active'  => true,
                ]);
            }
        }
    }
}
