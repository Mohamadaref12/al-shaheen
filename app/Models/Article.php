<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Support\ImageStorage;
use App\Traits\InteractsWithEnArTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
use App\Models\ArticleAiSuggestion;

class Article extends Model
{
    use InteractsWithEnArTranslations;
    use Translatable;

    public array $translatedAttributes = [
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
    ];

    protected $appends = ['featured_image_url'];

    protected $fillable = [
        'author_id',
        'primary_category_id',
        'writer_notes',
        'featured_image',
        'video_embed',
        'read_time',
        'is_breaking',
        'status',
        'approved_by',
        'is_premium',
        'is_editor_pick',
        'editor_pick_order',
        'views_count',
        'submitted_at',
        'approved_at',
        'scheduled_at',
        'published_at',
    ];

    public function translationModelClass(): string
    {
        return ArticleTranslation::class;
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(fn () => ImageStorage::url($this->featured_image));
    }

    protected function casts(): array
    {
        return [
            'is_breaking'       => 'boolean',
            'is_premium'        => 'boolean',
            'is_editor_pick'    => 'boolean',
            'editor_pick_order' => 'integer',
            'views_count'       => 'integer',
            'read_time'         => 'integer',
            'submitted_at'      => 'datetime',
            'approved_at'       => 'datetime',
            'scheduled_at'      => 'datetime',
            'published_at'      => 'datetime',
        ];
    }

    public function getTitleAttribute(): ?string
    {
        return $this->getTranslatedAttribute('title');
    }

    public function getSubtitleAttribute(): ?string
    {
        return $this->getTranslatedAttribute('subtitle');
    }

    public function getSlugAttribute(): ?string
    {
        return $this->getTranslatedAttribute('slug');
    }

    public function getContentAttribute(): ?string
    {
        return $this->getTranslatedAttribute('content');
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->getTranslatedAttribute('excerpt');
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->getTranslatedAttribute('seo_title');
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getTranslatedAttribute('seo_description');
    }

    public function getDisplayTitleAttribute(): string
    {
        if ($this->relationLoaded('translations')) {
            $arabic = $this->translations->firstWhere('locale', 'ar')?->title;
            $english = $this->translations->firstWhere('locale', 'en')?->title;

            return $arabic ?: $english ?: 'Article #' . $this->getKey();
        }

        return $this->translate('ar', false)?->title
            ?? $this->translate('en', false)?->title
            ?? 'Article #' . $this->getKey();
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

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(ArticleAiSuggestion::class);
    }
}
