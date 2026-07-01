<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug'       => 'journalism',
                'name_en'    => 'Journalism',
                'name_ar'    => 'الصحافة',
                'icon'       => 'pencil',
                'sort_order' => 1,
            ],
            [
                'slug'       => 'media',
                'name_en'    => 'Media',
                'name_ar'    => 'الإعلام',
                'icon'       => 'video-camera',
                'sort_order' => 2,
            ],
            [
                'slug'       => 'research',
                'name_en'    => 'Research',
                'name_ar'    => 'البحث',
                'icon'       => 'search',
                'sort_order' => 3,
            ],
            [
                'slug'       => 'photography',
                'name_en'    => 'Photography',
                'name_ar'    => 'التصوير',
                'icon'       => 'camera',
                'sort_order' => 4,
            ],
            [
                'slug'       => 'writing',
                'name_en'    => 'Writing',
                'name_ar'    => 'الكتابة',
                'icon'       => 'pen-nib',
                'sort_order' => 5,
            ],
            [
                'slug'       => 'video',
                'name_en'    => 'Video',
                'name_ar'    => 'الفيديو',
                'icon'       => 'film',
                'sort_order' => 6,
            ],
            [
                'slug'       => 'language',
                'name_en'    => 'Language',
                'name_ar'    => 'اللغة',
                'icon'       => 'globe',
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $category) {
            $slug = $category['slug'];

            $record = CourseCategory::query()
                ->whereHas('translations', fn ($q) => $q->where('slug', $slug)->where('locale', 'en'))
                ->first() ?? new CourseCategory();

            $record->fill([
                'icon'       => $category['icon'],
                'sort_order' => $category['sort_order'],
                'is_active'  => true,
            ])->save();

            $record->name_en = $category['name_en'];
            $record->slug_en = $slug;
            $record->name_ar = $category['name_ar'];
            $record->slug_ar = $slug;
            $record->save();
        }
    }
}
