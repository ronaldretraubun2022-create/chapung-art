<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use ManagesImageUploads;

    protected array $imageUploads = [
        'image' => 'public',
    ];

    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
        'content',
        'image',
        'button_text',
        'button_url',
        'sort_order',
        'is_active',
        'payload',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'payload' => 'array',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            PerformanceCache::flushHomepage();
        });

        static::deleted(function (): void {
            PerformanceCache::flushHomepage();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
