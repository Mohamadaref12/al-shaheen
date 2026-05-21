<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'author_id',
        'category_id',
        'guest_name',
        'guest_title',
        'guest_photo',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'video_embed',
        'is_premium',
        'locale',
        'status',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'   => 'boolean',
            'views_count'  => 'integer',
            'published_at' => 'datetime',
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
