<?php

namespace Database\Seeders;

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
        $courses = [
            [
                'title'      => 'Journalism Fundamentals',
                'category'   => 'Journalism Fundamentals',
                'level'      => 'beginner',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'Source Verification', 'duration_minutes' => 20],
                    ['title' => 'News Story Structure', 'duration_minutes' => 25],
                    ['title' => 'Journalism Ethics', 'duration_minutes' => 18],
                ],
            ],
            [
                'title'      => 'Writing for Digital Spaces',
                'category'   => 'Writing for Digital',
                'level'      => 'intermediate',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'Crafting Compelling Headlines', 'duration_minutes' => 15],
                    ['title' => 'SEO Content Writing', 'duration_minutes' => 30],
                    ['title' => 'Mobile Content Formatting', 'duration_minutes' => 20],
                    ['title' => 'Writing Email Newsletters', 'duration_minutes' => 22],
                ],
            ],
            [
                'title'      => 'Editorial Standards',
                'category'   => 'Editorial Standards',
                'level'      => 'intermediate',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'Editorial Style Guide', 'duration_minutes' => 25],
                    ['title' => 'Editing and Proofreading', 'duration_minutes' => 20],
                    ['title' => 'Editorial Policy and Ethics', 'duration_minutes' => 15],
                ],
            ],
            [
                'title'      => 'Video & Multimedia',
                'category'   => 'Video & Multimedia',
                'level'      => 'intermediate',
                'is_premium' => true,
                'lessons'    => [
                    ['title' => 'Writing Video Scripts', 'duration_minutes' => 30],
                    ['title' => 'On-Camera Presence', 'duration_minutes' => 25],
                    ['title' => 'Short-Form Social Media Video Production', 'duration_minutes' => 35],
                ],
            ],
            [
                'title'      => 'Investigative Journalism',
                'category'   => 'Investigative Reporting',
                'level'      => 'senior',
                'is_premium' => true,
                'lessons'    => [
                    ['title' => 'Data Journalism', 'duration_minutes' => 40],
                    ['title' => 'Source Protection and Information Security', 'duration_minutes' => 35],
                    ['title' => 'Freedom of Information Requests', 'duration_minutes' => 28],
                    ['title' => 'Building an Investigative Story', 'duration_minutes' => 45],
                ],
            ],
            [
                'title'      => 'New Contributor Guide',
                'category'   => 'Contributor Onboarding',
                'level'      => 'beginner',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'How to Submit Your Article', 'duration_minutes' => 10],
                    ['title' => 'Content Review Stages', 'duration_minutes' => 12],
                    ['title' => 'Acceptance and Rejection Criteria', 'duration_minutes' => 15],
                ],
            ],
        ];

        $users = User::whereHas('reader')->orWhereHas('contributor')->orWhereHas('writer')->pluck('id')->toArray();

        foreach ($courses as $courseData) {
            $lessonsData = $courseData['lessons'];
            unset($courseData['lessons']);

            $course = TrainingCourse::create([
                ...$courseData,
                'slug'      => Str::slug($courseData['title']) ?: 'course-' . rand(100, 999),
                'is_active' => true,
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

        // User progress
        $allLessons = TrainingLesson::all();
        foreach (array_slice($users, 0, 10) as $userId) {
            $randomLessons = $allLessons->random(rand(2, 6));
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
