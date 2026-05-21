<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Writer extends Model
{
    protected $table = 'writer';

    protected $fillable = [
        'user_id',
        'display_name',
        'bio',
        'profile_photo',
        'portfolio_link',
        'experience_level',
        'languages',
        'editorial_specialties',
        'location',
        'social_links',
        'is_verified_writer',
        'id_verification_file',
        'media_affiliation',
        'sample_publications',
        'application_status',
        'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'languages'             => 'array',
            'editorial_specialties' => 'array',
            'social_links'          => 'array',
            'sample_publications'   => 'array',
            'is_verified_writer'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'contributor_categories', 'contributor_id', 'category_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'writer_id', 'follower_id');
    }

    public function contentSubmissions(): HasMany
    {
        return $this->hasMany(ContentSubmission::class, 'writer_id');
    }
}

