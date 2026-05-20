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
        $authors = User::whereIn('role', ['editor', 'admin'])->pluck('id')->toArray();

        $events = [
            ['title' => 'مؤتمر الإعلام الرقمي 2026',          'location' => 'الكويت'],
            ['title' => 'ملتقى الصحافة الاستقصائية',           'location' => 'الرياض'],
            ['title' => 'ورشة كتابة المحتوى الإبداعي',         'location' => 'دبي'],
            ['title' => 'قمة ريادة الأعمال والإعلام',          'location' => 'أبوظبي'],
            ['title' => 'منتدى حرية الصحافة العربية',          'location' => 'بيروت'],
            ['title' => 'ندوة الذكاء الاصطناعي في الصحافة',   'location' => 'عمّان'],
        ];

        foreach ($events as $i => $data) {
            $startsAt = now()->addDays(rand(-10, 60));
            Event::create([
                'author_id'    => fake()->randomElement($authors),
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']) ?: 'event-' . ($i + 1),
                'description'  => fake('ar_SA')->paragraph(3),
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
