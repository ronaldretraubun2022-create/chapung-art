<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Support\HtmlSanitizer;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artist extends Model
{
    use ManagesImageUploads;
    use SoftDeletes;

    protected array $imageUploads = [
        'photo' => 'public',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'photo',
        'bio',
        'origin_area',
        'city',
        'province',
        'country',
        'birth_date',
        'email',
        'phone',
        'whatsapp',
        'instagram',
        'facebook',
        'website',
        'specialization',
        'education',
        'achievements',
        'exhibitions',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
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

    public function setBioAttribute(?string $value): void
    {
        $this->attributes['bio'] = HtmlSanitizer::clean($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function photographies(): HasMany
    {
        return $this->hasMany(Photography::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
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
