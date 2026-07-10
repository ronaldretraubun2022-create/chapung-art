<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use ManagesImageUploads;
    use SoftDeletes;

    protected array $imageUploads = [
        'cover_image' => 'public',
        'banner_image' => 'public',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'banner_image',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            PerformanceCache::flushContent();
        });

        static::deleted(function (): void {
            PerformanceCache::flushContent();
        });
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function photographies(): HasMany
    {
        return $this->hasMany(Photography::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
