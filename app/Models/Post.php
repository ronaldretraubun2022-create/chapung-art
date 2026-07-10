<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    protected $fillable = [
        'title',
        'author_id',
        'category_id',
        'slug',
        'excerpt',
        'featured_image',
        'content',
        'author_name',
        'status',
        'published_at',
        'scheduled_at',
        'reading_time',
        'views',
        'seo_title',
        'seo_description',
        'og_image',
        'thumbnail',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'reading_time' => 'integer',
        'views' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (Post $post): void {
            if ($post->status !== 'published') {
                return;
            }

            if (! $post->wasRecentlyCreated && ! $post->wasChanged('status')) {
                return;
            }

            AdminNotification::create([
                'title' => 'Post published',
                'message' => "Post {$post->title} telah dipublish.",
                'type' => 'post',
                'url' => url('/admin/posts/'.$post->id.'/edit'),
            ]);
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function mediaItems(): MorphMany
    {
        return $this->morphMany(MediaItem::class, 'mediable')->orderBy('sort_order');
    }

    public function pageViews(): MorphMany
    {
        return $this->morphMany(PageView::class, 'viewable');
    }

    public function getDisplayImageAttribute(): ?string
    {
        return $this->featured_image ?: $this->thumbnail;
    }
}
