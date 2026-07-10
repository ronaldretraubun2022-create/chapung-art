<?php

namespace App\Models;

use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;

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
}
