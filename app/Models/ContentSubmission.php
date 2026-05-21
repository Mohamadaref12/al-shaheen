<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Writer;
use App\Models\Article;

class ContentSubmission extends Model
{
    protected $fillable = [
        'writer_id',
        'reviewer_id',
        'article_id',
        'title',
        'subtitle',
        'content',
        'type',
        'status',
        'reviewer_notes',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at'  => 'datetime',
        ];
    }

    public function writer(): BelongsTo
    {
        return $this->belongsTo(Writer::class, 'writer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
