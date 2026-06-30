<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'news_id',
        'locale',
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
