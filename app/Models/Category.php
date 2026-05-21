<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Article;
use App\Models\Report;
use App\Models\Writer;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
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
