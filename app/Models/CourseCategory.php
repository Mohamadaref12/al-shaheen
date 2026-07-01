<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Traits\InteractsWithEnArTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseCategory extends Model
{
    use InteractsWithEnArTranslations;
    use Translatable;

    public array $translatedAttributes = [
        'name',
        'slug',
    ];

    protected $fillable = [
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function translationModelClass(): string
    {
        return CourseCategoryTranslation::class;
    }

    public function getNameAttribute(): ?string
    {
        return $this->getTranslatedAttribute('name');
    }

    public function getSlugAttribute(): ?string
    {
        return $this->getTranslatedAttribute('slug');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('translations')) {
            $arabic = $this->translations->firstWhere('locale', 'ar')?->name;
            $english = $this->translations->firstWhere('locale', 'en')?->name;

            return $arabic ?: $english ?: 'Category #' . $this->getKey();
        }

        return $this->translate('ar', false)?->name
            ?? $this->translate('en', false)?->name
            ?? 'Category #' . $this->getKey();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class, 'course_category_id');
    }
}
