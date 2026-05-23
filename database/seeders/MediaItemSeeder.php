<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaItemSeeder extends Seeder
{
    public function run(): void
    {
        $authors    = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();

        $mediaItems = [
            // video
            ['type' => 'video', 'title' => 'Video Report: The Future of Digital Media in the Gulf', 'duration' => 480],
            ['type' => 'video', 'title' => 'Video: Top News Events of the Week',                    'duration' => 360],
            ['type' => 'video', 'title' => 'Documentary: Investigative Journalism in the Arab World','duration' => 2700],
            ['type' => 'video', 'title' => 'Special Episode: Press Freedom in 2026',                 'duration' => 1800],
            ['type' => 'video', 'title' => 'News Bulletin: Top International Events',                'duration' => 600],
            // podcast / audio
            ['type' => 'audio', 'title' => 'Podcast: Editor Secrets - Episode One',                  'duration' => 2400],
            ['type' => 'audio', 'title' => 'Podcast: Story Writing - Between Art and Craft',         'duration' => 3000],
            ['type' => 'audio', 'title' => 'Podcast: Fact-Checking Fake News',                       'duration' => 2100],
            // gallery
            ['type' => 'gallery', 'title' => 'Gallery: Annual Media Professionals Event Photos',     'duration' => 0],
            ['type' => 'gallery', 'title' => 'Gallery: Top News Photos of 2026',                     'duration' => 0],
            ['type' => 'gallery', 'title' => 'Gallery: Behind the Scenes in the Newsroom',           'duration' => 0],
        ];

        $statuses = ['published', 'published', 'published', 'draft', 'archived'];

        foreach ($mediaItems as $i => $item) {
            $status = fake()->randomElement($statuses);

            MediaItem::create([
                'author_id'       => fake()->randomElement($authors),
                'category_id'     => fake()->randomElement($categories),
                'title'           => $item['title'],
                'slug'            => Str::slug($item['title']) ?: 'media-' . ($i + 1),
                'description'     => fake()->paragraph(2),
                'type'            => $item['type'],
                'media_url'       => 'https://example.com/media/' . Str::slug($item['title']),
                'duration_seconds'=> $item['duration'],
                'is_premium'      => fake()->boolean(25),
                'locale'          => fake()->randomElement(['ar', 'ar', 'en']),
                'status'          => $status,
                'published_at'    => $status === 'published' ? now()->subDays(rand(1, 60)) : null,
            ]);
        }
    }
}
