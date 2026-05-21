<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Category;

class Report extends Model
{
    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'file_url',
        'is_premium',
        'views_count',
        'locale',
        'status',
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
