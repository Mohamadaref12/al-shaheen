<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Clear old data ----
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
            'news',
            'media_items',
            'events',
            'writer',
            'categories',
            'tags',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- Seed data ----
        $this->call([
            CategorySeeder::class,          // 1. Categories & subcategories
            TagSeeder::class,               // 2. Tags
            UserSeeder::class,              // 3. Users
            WriterProfileSeeder::class,     // 4. Writer profiles
            ArticleSeeder::class,           // 5. Articles + comments
            ReportSeeder::class,            // 6. Reports
            InterviewSeeder::class,         // 7. Interviews
            NewsSeeder::class,              // 8. News
            MediaItemSeeder::class,         // 9. Media items
            EventSeeder::class,             // 10. Events
            MonetizationSeeder::class,      // 11. Ads + packages + subscriptions + newsletter
            PaymentSeeder::class,           // 12. Payments
            ContentSubmissionSeeder::class, // 13. Content submissions
            TrainingSeeder::class,          // 14. Courses + lessons + user progress
        ]);
    }
}
