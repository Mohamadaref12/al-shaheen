<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'course_category_id',
        'locale',
        'name',
        'slug',
    ];

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }
}
