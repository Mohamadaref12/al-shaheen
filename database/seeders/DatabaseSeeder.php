<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,          // 1. أقسام وتصنيفات
            TagSeeder::class,               // 2. تاجات
            UserSeeder::class,              // 3. مستخدمون (admin, editor, writers, readers)
            WriterProfileSeeder::class,     // 4. بروفايلات الكتّاب
            ArticleSeeder::class,           // 5. مقالات + تعليقات
            ReportSeeder::class,            // 6. تقارير
            EventSeeder::class,             // 7. فعاليات
            MonetizationSeeder::class,      // 8. إعلانات + باقات + اشتراكات + نشرة
            ContentSubmissionSeeder::class, // 9. طلبات محتوى
            TrainingSeeder::class,          // 10. دورات + دروس + تقدم المستخدم
        ]);
    }
}

