<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;

class Article extends Model
{
    protected $fillable = [
        'author_id',
        'primary_category_id',
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'video_embed',
        'locale',
        'read_time',
        'is_breaking',
        'status',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_breaking' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
            'read_time' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function secondaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_secondary_categories');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_articles')->withTimestamps();
    }
}
