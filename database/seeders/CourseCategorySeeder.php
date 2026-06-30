<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Journalism',  'slug' => 'journalism',  'icon' => 'pencil',       'sort_order' => 1],
            ['name' => 'Media',       'slug' => 'media',       'icon' => 'video-camera', 'sort_order' => 2],
            ['name' => 'Research',    'slug' => 'research',    'icon' => 'search',       'sort_order' => 3],
            ['name' => 'Photography', 'slug' => 'photography', 'icon' => 'camera',       'sort_order' => 4],
            ['name' => 'Writing',     'slug' => 'writing',     'icon' => 'pen-nib',      'sort_order' => 5],
            ['name' => 'Video',       'slug' => 'video',       'icon' => 'film',         'sort_order' => 6],
            ['name' => 'Language',    'slug' => 'language',    'icon' => 'globe',        'sort_order' => 7],
        ];

        foreach ($categories as $category) {
            CourseCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'is_active' => true]
            );
        }
    }
}
