<?php

namespace App\Models;

use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
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
}
