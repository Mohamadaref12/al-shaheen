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
            ['name' => 'News',       'slug' => 'news'],
            ['name' => 'Reports',    'slug' => 'reports'],
            ['name' => 'Interviews', 'slug' => 'interviews'],
            ['name' => 'Opinion',    'slug' => 'opinion'],
            ['name' => 'Multimedia', 'slug' => 'multimedia'],
            ['name' => 'Training',   'slug' => 'training'],
        ];

        $sub = [
            'news' => [
                ['name' => 'Local',         'slug' => 'local'],
                ['name' => 'Arab World',    'slug' => 'arab'],
                ['name' => 'International', 'slug' => 'international'],
                ['name' => 'Economy',       'slug' => 'economy'],
                ['name' => 'Sports',        'slug' => 'sports'],
                ['name' => 'Technology',    'slug' => 'tech'],
            ],
            'reports' => [
                ['name' => 'Investigations', 'slug' => 'investigations'],
                ['name' => 'Analysis',       'slug' => 'analysis'],
                ['name' => 'Statistics',     'slug' => 'statistics'],
            ],
            'opinion' => [
                ['name' => 'Opinion Articles', 'slug' => 'opinion-articles'],
                ['name' => 'Commentary',       'slug' => 'commentary'],
            ],
            'multimedia' => [
                ['name' => 'Video',       'slug' => 'video'],
                ['name' => 'Podcast',     'slug' => 'podcast'],
                ['name' => 'Infographic', 'slug' => 'infographic'],
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
