<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'sku',
        'category_id',
        'artist_id',
        'collection_id',
        'slug',
        'excerpt',
        'description',
        'artist_name',
        'price',
        'status',
        'medium',
        'material',
        'technique',
        'orientation',
        'frame',
        'size',
        'width',
        'height',
        'depth',
        'weight',
        'condition',
        'location',
        'certificate_number',
        'license',
        'stock',
        'views',
        'likes',
        'seo_title',
        'seo_description',
        'og_image',
        'year',
        'thumbnail',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'depth' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock' => 'integer',
        'views' => 'integer',
        'likes' => 'integer',
        'is_featured' => 'boolean',
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

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function pageViews(): MorphMany
    {
        return $this->morphMany(PageView::class, 'viewable');
    }

    public function getArtistDisplayNameAttribute(): ?string
    {
        return $this->artist?->name ?: $this->artist_name;
    }
}
