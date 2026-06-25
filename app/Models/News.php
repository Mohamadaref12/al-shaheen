<?php

namespace App\Models;

use App\Support\ImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $appends = ['featured_image_url'];

    protected $fillable = [
        'author_id',
        'category_id',
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
        'is_premium',
        'status',
        'views_count',
        'seo_title',
        'seo_description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_breaking' => 'boolean',
            'is_premium' => 'boolean',
            'read_time' => 'integer',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(fn () => ImageStorage::url($this->featured_image));
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
}
