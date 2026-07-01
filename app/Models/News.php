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

class News extends Model
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
        'category_id',
        'featured_image',
        'video_embed',
        'read_time',
        'is_breaking',
        'is_premium',
        'status',
        'views_count',
        'published_at',
    ];

    public function translationModelClass(): string
    {
        return NewsTranslation::class;
    }

    protected function casts(): array
    {
        return [
            'is_breaking' => 'boolean',
            'is_premium'  => 'boolean',
            'read_time'   => 'integer',
            'views_count' => 'integer',
            'published_at'=> 'datetime',
        ];
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(fn () => ImageStorage::url($this->featured_image));
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

            return $arabic ?: $english ?: 'News #' . $this->getKey();
        }

        return $this->translate('ar', false)?->title
            ?? $this->translate('en', false)?->title
            ?? 'News #' . $this->getKey();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'news_tags');
    }
}
