<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- مسح البيانات القديمة ----
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'user_course_progress',
            'article_views',
            'article_revisions',
            'follows',
            'saved_articles',
            'article_tags',
            'article_secondary_categories',
            'contributor_categories',
            'comments',
            'content_submissions',
            'payments',
            'subscriptions',
            'subscription_packages',
            'newsletter_subscribers',
            'ads',
            'training_lessons',
            'training_courses',
            'articles',
            'reports',
            'interviews',
            'media_items',
            'events',
            'pages',
            'site_settings',
            'writer',
            'categories',
            'tags',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- إدراج البيانات ----
        $this->call([
            CategorySeeder::class,          // 1. أقسام وتصنيفات
            TagSeeder::class,               // 2. تاجات
            UserSeeder::class,              // 3. مستخدمون
            WriterProfileSeeder::class,     // 4. بروفايلات الكتّاب
            ArticleSeeder::class,           // 5. مقالات + تعليقات
            ReportSeeder::class,            // 6. تقارير
            InterviewSeeder::class,         // 7. مقابلات
            MediaItemSeeder::class,         // 8. وسائط متعددة
            EventSeeder::class,             // 9. فعاليات
            MonetizationSeeder::class,      // 10. إعلانات + باقات + اشتراكات + نشرة
            PaymentSeeder::class,           // 11. مدفوعات
            ContentSubmissionSeeder::class, // 12. طلبات محتوى
            TrainingSeeder::class,          // 13. دورات + دروس + تقدم المستخدم
            PageSeeder::class,              // 14. صفحات ثابتة
            SiteSettingSeeder::class,       // 15. إعدادات الموقع
        ]);
    }
}


