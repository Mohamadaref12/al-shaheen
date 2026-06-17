<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleAiSuggestion extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'focus',
        'locale',
        'original_snapshot',
        'suggestions',
        'notes',
        'provider',
        'model',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'original_snapshot' => 'array',
            'suggestions'       => 'array',
            'notes'             => 'array',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
