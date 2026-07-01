<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use App\Models\CourseCategoryTranslation;
use App\Models\TrainingCourse;
use App\Models\TrainingLesson;
use App\Models\User;
use App\Models\UserCourseProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = CourseCategoryTranslation::query()
            ->where('locale', 'en')
            ->pluck('course_category_id', 'slug');

        $courses = [
            [
                'title'       => 'Investigative Journalism Foundations',
                'slug'        => 'investigative-journalism-foundations',
                'category'    => 'journalism',
                'level'       => 'intermediate',
                'is_premium'  => true,
                'excerpt'     => 'Build the core skills needed to research, verify, and publish investigative stories with editorial rigor.',
                'description' => 'A practical course for journalists who want to move from daily reporting to in-depth investigations.',
                'price'       => 149.00,
                'original_price' => 249.00,
                'instructor_name'  => 'Al Saheen Editorial Desk',
                'instructor_label' => 'Group Sessions',
                'duration_weeks'   => 5,
                'downloadable_files_count' => 7,
                'rating'       => 4.8,
                'reviews_count'  => 42,
                'learning_outcomes' => [
                    'Plan and structure an investigative story from source to publication.',
                    'Verify documents, interviews, and digital evidence.',
                    'Protect sources and manage sensitive information.',
                    'Pitch investigative ideas to editors and newsrooms.',
                ],
                'lessons' => [
                    ['title' => 'Source Verification', 'duration_minutes' => 20],
                    ['title' => 'News Story Structure', 'duration_minutes' => 25],
                    ['title' => 'Journalism Ethics', 'duration_minutes' => 18],
                    ['title' => 'Data Journalism Basics', 'duration_minutes' => 40],
                ],
            ],
            [
                'title'      => 'Journalism Fundamentals',
                'category'   => 'journalism',
                'level'      => 'beginner',
                'is_premium' => false,
                'price'      => 79.00,
                'excerpt'    => 'Learn the essentials of news writing, sourcing, and editorial standards.',
                'lessons'    => [
                    ['title' => 'Source Verification', 'duration_minutes' => 20],
                    ['title' => 'News Story Structure', 'duration_minutes' => 25],
                    ['title' => 'Journalism Ethics', 'duration_minutes' => 18],
                ],
            ],
            [
                'title'      => 'Writing for Digital Spaces',
                'category'   => 'writing',
                'level'      => 'intermediate',
                'is_premium' => false,
                'price'      => 99.00,
                'excerpt'    => 'Craft headlines, SEO-friendly copy, and mobile-first content that performs.',
                'lessons'    => [
                    ['title' => 'Crafting Compelling Headlines', 'duration_minutes' => 15],
                    ['title' => 'SEO Content Writing', 'duration_minutes' => 30],
                    ['title' => 'Mobile Content Formatting', 'duration_minutes' => 20],
                    ['title' => 'Writing Email Newsletters', 'duration_minutes' => 22],
                ],
            ],
            [
                'title'      => 'Editorial Standards',
                'category'   => 'writing',
                'level'      => 'intermediate',
                'is_premium' => false,
                'price'      => 89.00,
                'excerpt'    => 'Master style guides, editing workflows, and editorial policy.',
                'lessons'    => [
                    ['title' => 'Editorial Style Guide', 'duration_minutes' => 25],
                    ['title' => 'Editing and Proofreading', 'duration_minutes' => 20],
                    ['title' => 'Editorial Policy and Ethics', 'duration_minutes' => 15],
                ],
            ],
            [
                'title'      => 'Video & Multimedia',
                'category'   => 'video',
                'level'      => 'intermediate',
                'is_premium' => true,
                'price'      => 129.00,
                'excerpt'    => 'Produce short-form video content for newsrooms and social platforms.',
                'lessons'    => [
                    ['title' => 'Writing Video Scripts', 'duration_minutes' => 30],
                    ['title' => 'On-Camera Presence', 'duration_minutes' => 25],
                    ['title' => 'Short-Form Social Media Video Production', 'duration_minutes' => 35],
                ],
            ],
            [
                'title'      => 'Media Production Essentials',
                'category'   => 'media',
                'level'      => 'beginner',
                'is_premium' => false,
                'price'      => 69.00,
                'excerpt'    => 'Understand the fundamentals of multimedia storytelling in modern newsrooms.',
                'lessons'    => [
                    ['title' => 'Multimedia Story Planning', 'duration_minutes' => 22],
                    ['title' => 'Audio for Journalists', 'duration_minutes' => 18],
                    ['title' => 'Visual Storytelling Basics', 'duration_minutes' => 24],
                ],
            ],
        ];

        $users = User::whereHas('reader')->orWhereHas('contributor')->orWhereHas('writer')->pluck('id')->toArray();

        foreach ($courses as $index => $courseData) {
            $lessonsData = $courseData['lessons'];
            $categorySlug = $courseData['category'];
            unset($courseData['lessons'], $courseData['category']);

            $course = TrainingCourse::create([
                ...$courseData,
                'slug'                => $courseData['slug'] ?? Str::slug($courseData['title']),
                'course_category_id'  => $categoryMap[$categorySlug] ?? null,
                'currency'            => 'USD',
                'has_lifetime_access' => true,
                'is_active'           => true,
                'sort_order'          => $index + 1,
                'about_content'       => $courseData['about_content'] ?? fake()->paragraphs(3, true),
            ]);

            foreach ($lessonsData as $order => $lessonData) {
                TrainingLesson::create([
                    'course_id'        => $course->id,
                    'title'            => $lessonData['title'],
                    'description'      => fake()->sentence(),
                    'video_url'        => null,
                    'duration_minutes' => $lessonData['duration_minutes'],
                    'sort_order'       => $order + 1,
                    'is_premium'       => $course->is_premium,
                ]);
            }
        }

        $allLessons = TrainingLesson::all();
        foreach (array_slice($users, 0, 10) as $userId) {
            $randomLessons = $allLessons->random(min(rand(2, 6), $allLessons->count()));
            foreach ($randomLessons as $lesson) {
                $completed = fake()->boolean(60);
                UserCourseProgress::firstOrCreate(
                    ['user_id' => $userId, 'lesson_id' => $lesson->id],
                    [
                        'course_id'    => $lesson->course_id,
                        'is_completed' => $completed,
                        'completed_at' => $completed ? now()->subDays(rand(1, 30)) : null,
                    ]
                );
            }
        }
    }
}
