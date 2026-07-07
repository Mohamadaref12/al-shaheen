<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpinionTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'opinion_id',
        'locale',
        'title',
        'subtitle',
        'slug',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
    ];

    public function opinion(): BelongsTo
    {
        return $this->belongsTo(Opinion::class);
    }
}
