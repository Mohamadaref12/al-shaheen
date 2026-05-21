<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItem extends Model
{
    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'description',
        'type',
        'media_url',
        'thumbnail',
        'duration_seconds',
        'transcript',
        'is_premium',
        'locale',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'       => 'boolean',
            'duration_seconds' => 'integer',
            'published_at'     => 'datetime',
        ];
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
