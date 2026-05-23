<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::whereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();

        $events = [
            ['title' => 'Digital Media Conference 2026',         'location' => 'الكويت'],
            ['title' => 'Investigative Journalism Forum',          'location' => 'الرياض'],
            ['title' => 'Creative Content Writing Workshop',       'location' => 'دبي'],
            ['title' => 'Media & Entrepreneurship Summit',         'location' => 'أبوظبي'],
            ['title' => 'Arab Press Freedom Forum',               'location' => 'بيروت'],
            ['title' => 'AI in Journalism Seminar',               'location' => 'عمّان'],
        ];

        foreach ($events as $i => $data) {
            $startsAt = now()->addDays(rand(-10, 60));
            Event::create([
                'author_id'    => fake()->randomElement($authors),
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']) ?: 'event-' . ($i + 1),
                'description'  => fake()->paragraph(3),
                'image'        => null,
                'location'     => $data['location'],
                'starts_at'    => $startsAt,
                'ends_at'      => $startsAt->copy()->addDays(rand(1, 3)),
                'external_url' => fake()->url(),
                'is_featured'  => fake()->boolean(30),
            ]);
        }
    }
}
