<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Writer;
use App\Models\Reader;
use App\Models\Contributor;
use App\Models\Editor;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Report;
use App\Models\Event;
use App\Models\Interview;
use App\Models\MediaItem;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\ContentSubmission;
use App\Models\Comment;
use App\Models\UserCourseProgress;

#[Fillable(['name', 'email', 'password', 'country', 'language', 'locale', 'is_verified', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function writer(): HasOne
    {
        return $this->hasOne(Writer::class);
    }

    public function reader(): HasOne
    {
        return $this->hasOne(Reader::class);
    }

    public function contributor(): HasOne
    {
        return $this->hasOne(Contributor::class);
    }

    public function editor(): HasOne
    {
        return $this->hasOne(Editor::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'author_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'author_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'author_id');
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class, 'author_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function submittedContent(): HasMany
    {
        return $this->hasMany(ContentSubmission::class, 'writer_id');
    }

    public function reviewedContent(): HasMany
    {
        return $this->hasMany(ContentSubmission::class, 'reviewer_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function courseProgress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class);
    }

    public function savedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'saved_articles')->withPivot('created_at');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(Writer::class, 'follows', 'follower_id', 'writer_id')
            ->withPivot('created_at');
    }
}
