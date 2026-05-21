<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TrainingLesson;
use App\Models\UserCourseProgress;

class TrainingCourse extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'level',
        'thumbnail',
        'is_premium',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(TrainingLesson::class, 'course_id');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class, 'course_id');
    }
}
