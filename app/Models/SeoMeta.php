<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class SeoMeta extends Model
{
    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'route_name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'robots',
        'schema_json',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('seo_metas'));
        static::deleted(fn (): bool => Cache::forget('seo_metas'));
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
