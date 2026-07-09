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
            'article_translations',
            'news_translations',
            'opinion_translations',
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
            'course_category_translations',
            'course_categories',
            'articles',
            'reports',
            'interviews',
            'news',
            'opinions',
            'media_items',
            'events',
            'contributor_profile_categories',
            'contributors',
            'readers',
            'editors',
            'admins',
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
            FeaturedImageSeeder::class,     // 8b. Default banner on articles & news
            OpinionSeeder::class,           // 9. Opinions
            MediaItemSeeder::class,         // 10. Media items
            EventSeeder::class,             // 11. Events
            MonetizationSeeder::class,      // 12. Ads + packages + subscriptions + newsletter
            PaymentSeeder::class,           // 13. Payments
            ContentSubmissionSeeder::class, // 14. Content submissions
            CourseCategorySeeder::class,    // 15. Course categories
            TrainingSeeder::class,          // 16. Courses + lessons + user progress
        ]);
    }
}
