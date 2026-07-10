<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Photography extends Model
{
    protected $fillable = [
        'title',
        'category_id',
        'artist_id',
        'collection_id',
        'slug',
        'excerpt',
        'description',
        'photographer_name',
        'location',
        'province',
        'country',
        'camera',
        'lens',
        'iso',
        'aperture',
        'shutter_speed',
        'focal_length',
        'gps_lat',
        'gps_lng',
        'taken_at',
        'license',
        'price',
        'stock',
        'views',
        'seo_title',
        'seo_description',
        'og_image',
        'status',
        'thumbnail',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'gps_lat' => 'decimal:7',
        'gps_lng' => 'decimal:7',
        'taken_at' => 'datetime',
        'stock' => 'integer',
        'views' => 'integer',
        'iso' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
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

    public function getArtistDisplayNameAttribute(): ?string
    {
        return $this->artist?->name ?: $this->photographer_name;
    }
}
