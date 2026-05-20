<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ContentSubmission extends Model
{
    protected $fillable = [
        'writer_id',
        'reviewer_id',
        'title',
        'subtitle',
        'content',
        'type',
        'status',
        'reviewer_notes',
    ];

    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
