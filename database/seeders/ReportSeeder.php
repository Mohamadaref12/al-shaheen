<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $writers    = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $categories = Category::whereNull('parent_id')->pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            $title  = fake('ar_SA')->sentence(rand(6, 10));
            $status = fake()->randomElement(['published', 'published', 'draft']);

            Report::create([
                'author_id'      => fake()->randomElement($writers),
                'category_id'    => fake()->randomElement($categories),
                'title'          => $title,
                'slug'           => Str::slug($title) ?: 'report-' . ($i + 1),
                'content'        => implode("\n\n", fake('ar_SA')->paragraphs(rand(6, 12))),
                'excerpt'        => fake('ar_SA')->paragraph(2),
                'featured_image' => null,
                'file_url'       => null,
                'is_premium'     => fake()->boolean(30),
                'locale'         => fake()->randomElement(['ar', 'en']),
                'status'         => $status,
                'published_at'   => $status === 'published' ? now()->subDays(rand(1, 90)) : null,
            ]);
        }
    }
}
