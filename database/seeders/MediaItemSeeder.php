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
            // فيديو
            ['type' => 'video', 'title' => 'تقرير مرئي: مستقبل الإعلام الرقمي في الخليج', 'duration' => 480],
            ['type' => 'video', 'title' => 'فيديو: أبرز أحداث الأسبوع الإخبارية', 'duration' => 360],
            ['type' => 'video', 'title' => 'وثائقي: الصحافة الاستقصائية في العالم العربي', 'duration' => 2700],
            ['type' => 'video', 'title' => 'حلقة خاصة: حرية الصحافة في 2026', 'duration' => 1800],
            ['type' => 'video', 'title' => 'نشرة الأخبار: أبرز الأحداث الدولية', 'duration' => 600],
            // بودكاست / صوت
            ['type' => 'audio', 'title' => 'بودكاست: أسرار المحرر - الحلقة الأولى', 'duration' => 2400],
            ['type' => 'audio', 'title' => 'بودكاست: كتابة القصة - بين الفن والمهنة', 'duration' => 3000],
            ['type' => 'audio', 'title' => 'بودكاست: التحقق من الأخبار الكاذبة', 'duration' => 2100],
            // معرض صور
            ['type' => 'gallery', 'title' => 'معرض: صور الحدث السنوي للإعلاميين', 'duration' => 0],
            ['type' => 'gallery', 'title' => 'معرض: أبرز الصور الإخبارية لعام 2026', 'duration' => 0],
            ['type' => 'gallery', 'title' => 'معرض: كواليس غرفة الأخبار', 'duration' => 0],
        ];

        $statuses = ['published', 'published', 'published', 'draft', 'archived'];

        foreach ($mediaItems as $i => $item) {
            $status = fake()->randomElement($statuses);

            MediaItem::create([
                'author_id'       => fake()->randomElement($authors),
                'category_id'     => fake()->randomElement($categories),
                'title'           => $item['title'],
                'slug'            => Str::slug($item['title']) ?: 'media-' . ($i + 1),
                'description'     => fake('ar_SA')->paragraph(2),
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
