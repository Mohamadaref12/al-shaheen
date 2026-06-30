<?php

namespace App\Traits;

use App\Models\CourseCategory;
use App\Models\TrainingCourse;
use Illuminate\Database\Eloquent\Builder;

trait QueriesTrainingCourses
{
    protected function trainingCourseBaseQuery(): Builder
    {
        return TrainingCourse::query()
            ->with('category')
            ->withCount('lessons')
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->where('is_active', true);
    }

    protected function applyTrainingCourseFilters(Builder $query, \Illuminate\Http\Request $request): Builder
    {
        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->whereHas('category', fn ($q) => $q
                ->where('slug', $category)
                ->orWhere('id', $category));
        }

        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        if ($request->boolean('premium')) {
            $query->where('is_premium', true);
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"));
        }

        match ($request->input('sort', 'default')) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating'     => $query->orderByDesc('rating')->orderByDesc('reviews_count'),
            'newest'     => $query->orderByDesc('created_at'),
            default      => $query->orderBy('sort_order')->orderBy('title'),
        };

        return $query;
    }

    protected function findActiveTrainingCourse(string $identifier, array $with = []): ?TrainingCourse
    {
        return TrainingCourse::query()
            ->with(array_merge(['category'], $with))
            ->withCount('lessons')
            ->withSum('lessons as total_duration_minutes', 'duration_minutes')
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where('id', $identifier)
                ->orWhere('slug', $identifier))
            ->first();
    }

    protected function findActiveCourseCategory(string $identifier): ?CourseCategory
    {
        return CourseCategory::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where('id', $identifier)
                ->orWhere('slug', $identifier))
            ->first();
    }

    protected function relatedCoursesFor(TrainingCourse $course, int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return $this->trainingCourseBaseQuery()
            ->where('id', '!=', $course->id)
            ->when(
                $course->course_category_id,
                fn ($q) => $q->where('course_category_id', $course->course_category_id)
            )
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}
