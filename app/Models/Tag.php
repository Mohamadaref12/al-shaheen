<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Article;
use App\Models\News;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tags');
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_tags');
    }
}
