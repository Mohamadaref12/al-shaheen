<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'سياسة', 'اقتصاد', 'تقنية', 'صحة', 'تعليم', 'بيئة',
            'رياضة', 'ثقافة', 'فن', 'سفر', 'عقارات', 'طاقة',
            'ذكاء اصطناعي', 'ريادة أعمال', 'مال وأعمال', 'قضاء',
            'دبلوماسية', 'أمن', 'مجتمع', 'صحافة',
        ];

        foreach ($tags as $name) {
            Tag::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name, '-', 'ar') ?: \Illuminate\Support\Str::slug($name),
            ]);
        }
    }
}
