<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\HtmlSanitizer;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Artwork extends Model
{
    use HasFactory;
    use ManagesImageUploads;

    protected array $imageUploads = [
        'og_image' => 'public',
        'thumbnail' => 'public',
    ];

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
        'digital_download_enabled',
        'digital_file_path',
        'digital_file_name',
        'digital_file_size',
        'digital_file_mime',
        'digital_file_uploaded_at',
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
        'digital_download_enabled' => 'boolean',
        'digital_file_size' => 'integer',
        'digital_file_uploaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Artwork $artwork): void {
            if (! $artwork->isDirty('digital_file_path')) {
                return;
            }

            $path = filled($artwork->digital_file_path) ? ltrim((string) $artwork->digital_file_path, '/') : null;
            $artwork->digital_file_path = $path;

            if (! $path) {
                $artwork->digital_file_name = null;
                $artwork->digital_file_size = null;
                $artwork->digital_file_mime = null;
                $artwork->digital_file_uploaded_at = null;

                return;
            }

            $disk = Storage::disk('local');

            if ($disk->exists($path)) {
                $artwork->digital_file_size = $disk->size($path);
                $artwork->digital_file_mime = $disk->mimeType($path) ?: $artwork->digital_file_mime;
            }

            $artwork->digital_file_uploaded_at ??= now();
        });

        static::saved(function (): void {
            PerformanceCache::flushContent();
        });

        static::updated(function (Artwork $artwork): void {
            if (! $artwork->wasChanged('digital_file_path')) {
                return;
            }

            $oldPath = $artwork->getOriginal('digital_file_path');

            if (filled($oldPath) && $oldPath !== $artwork->digital_file_path) {
                Storage::disk('local')->delete((string) $oldPath);
            }
        });

        static::deleted(function (Artwork $artwork): void {
            if (filled($artwork->digital_file_path)) {
                Storage::disk('local')->delete((string) $artwork->digital_file_path);
            }

            PerformanceCache::flushContent();
        });
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = HtmlSanitizer::clean($value);
    }

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

    public function reviews(): HasMany
    {
        return $this->hasMany(ArtworkReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->approved();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ArtworkFavorite::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artwork_favorites')->withTimestamps();
    }

    public function pageViews(): MorphMany
    {
        return $this->morphMany(PageView::class, 'viewable');
    }

    public function getArtistDisplayNameAttribute(): ?string
    {
        return $this->artist?->name ?: $this->artist_name;
    }

    public function hasDigitalDownload(): bool
    {
        return $this->digital_download_enabled && filled($this->digital_file_path);
    }
}
