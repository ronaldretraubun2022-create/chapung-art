<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\HtmlSanitizer;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use ManagesImageUploads;

    protected array $imageUploads = [
        'featured_image' => 'public',
        'og_image' => 'public',
        'thumbnail' => 'public',
    ];

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

    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = HtmlSanitizer::clean($value);
    }

    protected static function booted(): void
    {
        static::saved(function (Post $post): void {
            PerformanceCache::flushContent();

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

        static::deleted(function (): void {
            PerformanceCache::flushContent();
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
