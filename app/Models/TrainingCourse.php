<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TrainingLesson;
use App\Models\UserCourseProgress;
use App\Models\CourseEnrollment;

class TrainingCourse extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'course_category_id',
        'excerpt',
        'about_content',
        'about_image',
        'price',
        'original_price',
        'currency',
        'instructor_name',
        'instructor_avatar',
        'instructor_label',
        'duration_weeks',
        'downloadable_files_count',
        'has_lifetime_access',
        'learning_outcomes',
        'rating',
        'reviews_count',
        'video_preview_url',
        'level',
        'thumbnail',
        'is_premium',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'              => 'boolean',
            'is_active'               => 'boolean',
            'has_lifetime_access'     => 'boolean',
            'sort_order'              => 'integer',
            'duration_weeks'          => 'integer',
            'downloadable_files_count'=> 'integer',
            'reviews_count'           => 'integer',
            'price'                   => 'decimal:2',
            'original_price'          => 'decimal:2',
            'rating'                  => 'decimal:1',
            'learning_outcomes'       => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(TrainingLesson::class, 'course_id');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class, 'course_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'course_id');
    }
}
