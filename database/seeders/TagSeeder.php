<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Politics', 'Economy', 'Technology', 'Health', 'Education', 'Environment',
            'Sports', 'Culture', 'Art', 'Travel', 'Real Estate', 'Energy',
            'Artificial Intelligence', 'Entrepreneurship', 'Finance & Business', 'Judiciary',
            'Diplomacy', 'Security', 'Society', 'Journalism',
        ];

        foreach ($tags as $name) {
            Tag::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
            ]);
        }
    }
}
