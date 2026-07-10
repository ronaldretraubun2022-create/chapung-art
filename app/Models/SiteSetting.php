<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use ManagesImageUploads;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            PerformanceCache::flushSiteSettings();
        });

        static::deleted(function (): void {
            PerformanceCache::flushSiteSettings();
        });
    }

    protected function imageUploadAttributes(): array
    {
        if ($this->type !== 'image' && $this->getOriginal('type') !== 'image') {
            return [];
        }

        return ['value' => 'public'];
    }
}
