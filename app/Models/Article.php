<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\ArticleRevision;
use App\Models\ArticleView;

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
        'writer_notes',
        'featured_image',
        'video_embed',
        'locale',
        'read_time',
        'is_breaking',
        'status',
        'approved_by',
        'is_premium',
        'is_editor_pick',
        'editor_pick_order',
        'views_count',
        'seo_title',
        'seo_description',
        'submitted_at',
        'approved_at',
        'scheduled_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_breaking'  => 'boolean',
            'is_premium'     => 'boolean',
            'is_editor_pick' => 'boolean',
            'editor_pick_order' => 'integer',
            'views_count'    => 'integer',
            'read_time'    => 'integer',
            'submitted_at' => 'datetime',
            'approved_at'  => 'datetime',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_articles')->withTimestamps();
    }
}
