<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\Category;

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
        'id_verification',
        'media_affiliation',
        'sample_publications',
        'application_status',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'editorial_specialties' => 'array',
            'social_links' => 'array',
            'sample_publications' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'writer_categories', 'writer_id', 'category_id');
    }

}
