<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Traits\InteractsWithEnArTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use InteractsWithEnArTranslations;
    use Translatable;

    public array $translatedAttributes = [
        'name',
        'slug',
        'description',
    ];

    protected $fillable = [
        'parent_id',
        'image',
        'sort_order',
        'is_top_level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'is_top_level' => 'boolean',
        ];
    }

    public function translationModelClass(): string
    {
        return CategoryTranslation::class;
    }

    public function getNameAttribute(): ?string
    {
        return $this->getTranslatedAttribute('name');
    }

    public function getSlugAttribute(): ?string
    {
        return $this->getTranslatedAttribute('slug');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslatedAttribute('description');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->localizedDisplayValue('name', 'Category #'.$this->getKey());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'primary_category_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function writers(): BelongsToMany
    {
        return $this->belongsToMany(Writer::class, 'contributor_categories', 'category_id', 'contributor_id');
    }

    public function secondaryArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_secondary_categories');
    }
}
