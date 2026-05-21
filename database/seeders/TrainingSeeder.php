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
                'title'      => 'أساسيات الصحافة',
                'category'   => 'Journalism Fundamentals',
                'level'      => 'beginner',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'التحقق من المصادر', 'duration_minutes' => 20],
                    ['title' => 'بنية القصة الإخبارية', 'duration_minutes' => 25],
                    ['title' => 'أخلاقيات الصحافة', 'duration_minutes' => 18],
                ],
            ],
            [
                'title'      => 'الكتابة للفضاء الرقمي',
                'category'   => 'Writing for Digital',
                'level'      => 'intermediate',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'صياغة العناوين الجذابة', 'duration_minutes' => 15],
                    ['title' => 'كتابة المحتوى لمحركات البحث SEO', 'duration_minutes' => 30],
                    ['title' => 'تنسيق المحتوى للهاتف', 'duration_minutes' => 20],
                    ['title' => 'كتابة النشرات البريدية', 'duration_minutes' => 22],
                ],
            ],
            [
                'title'      => 'المعايير التحريرية',
                'category'   => 'Editorial Standards',
                'level'      => 'intermediate',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'دليل الأسلوب التحريري', 'duration_minutes' => 25],
                    ['title' => 'التحرير والمراجعة', 'duration_minutes' => 20],
                    ['title' => 'السياسة التحريرية والأخلاقيات', 'duration_minutes' => 15],
                ],
            ],
            [
                'title'      => 'الفيديو والوسائط المتعددة',
                'category'   => 'Video & Multimedia',
                'level'      => 'intermediate',
                'is_premium' => true,
                'lessons'    => [
                    ['title' => 'كتابة سيناريو الفيديو', 'duration_minutes' => 30],
                    ['title' => 'الظهور أمام الكاميرا', 'duration_minutes' => 25],
                    ['title' => 'إنتاج فيديو قصير للسوشيال ميديا', 'duration_minutes' => 35],
                ],
            ],
            [
                'title'      => 'الصحافة الاستقصائية',
                'category'   => 'Investigative Reporting',
                'level'      => 'senior',
                'is_premium' => true,
                'lessons'    => [
                    ['title' => 'الصحافة بالبيانات', 'duration_minutes' => 40],
                    ['title' => 'حماية المصادر وأمن المعلومات', 'duration_minutes' => 35],
                    ['title' => 'طلبات الحصول على المعلومات', 'duration_minutes' => 28],
                    ['title' => 'بناء قصة استقصائية', 'duration_minutes' => 45],
                ],
            ],
            [
                'title'      => 'دليل المساهم الجديد',
                'category'   => 'Contributor Onboarding',
                'level'      => 'beginner',
                'is_premium' => false,
                'lessons'    => [
                    ['title' => 'كيفية إرسال مقالك', 'duration_minutes' => 10],
                    ['title' => 'مراحل مراجعة المحتوى', 'duration_minutes' => 12],
                    ['title' => 'معايير القبول والرفض', 'duration_minutes' => 15],
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
                    'description'      => fake('ar_SA')->sentence(),
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
