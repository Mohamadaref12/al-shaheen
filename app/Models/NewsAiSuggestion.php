<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsAiSuggestion extends Model
{
    protected $fillable = [
        'news_id',
        'user_id',
        'kind',
        'focus',
        'locale',
        'source_locale',
        'target_locale',
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

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
