<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        $authors    = User::whereHas('writer')->orWhereHas('editor')->orWhereHas('admin')->pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();

        $guests = [
            ['name' => 'د. فاطمة الحربي',   'title' => 'خبيرة إعلام رقمي'],
            ['name' => 'م. خالد العنزي',    'title' => 'مدير إستراتيجيات المحتوى'],
            ['name' => 'أ. نورة المطيري',   'title' => 'صحفية استقصائية'],
            ['name' => 'د. عبدالله العتيبي','title' => 'أكاديمي متخصص في الإعلام'],
            ['name' => 'أ. سارة الدوسري',  'title' => 'مؤسسة منصة إعلامية رقمية'],
            ['name' => 'م. ماجد الشمري',   'title' => 'خبير تقنية المعلومات'],
            ['name' => 'د. منى الزهراني',  'title' => 'باحثة في الذكاء الاصطناعي والإعلام'],
            ['name' => 'أ. يوسف الغامدي',  'title' => 'مدير تحرير صحيفة وطنية'],
        ];

        $statuses = ['published', 'published', 'published', 'draft', 'under_review'];

        for ($i = 0; $i < 12; $i++) {
            $guest  = fake()->randomElement($guests);
            $title  = 'Interview with ' . $guest['name'] . ': ' . fake()->sentence(rand(3, 6));
            $status = fake()->randomElement($statuses);

            Interview::create([
                'author_id'      => fake()->randomElement($authors),
                'category_id'    => fake()->randomElement($categories),
                'guest_name'     => $guest['name'],
                'guest_title'    => $guest['title'],
                'title'          => $title,
                'slug'           => Str::slug($title) ?: 'interview-' . ($i + 1),
                'excerpt'        => fake()->paragraph(2),
                'content'        => implode("\n\n", fake()->paragraphs(rand(6, 12))),
                'locale'         => fake()->randomElement(['ar', 'ar', 'en']),
                'is_premium'     => fake()->boolean(30),
                'status'         => $status,
                'views_count'    => $status === 'published' ? rand(100, 8000) : 0,
                'published_at'   => $status === 'published' ? now()->subDays(rand(1, 90)) : null,
            ]);
        }
    }
}
