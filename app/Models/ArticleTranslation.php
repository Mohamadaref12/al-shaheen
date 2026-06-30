<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
