<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contributor extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'profile_photo',
        'portfolio_link',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'contributor_profile_categories',
            'contributor_id',
            'category_id'
        );
    }
}
