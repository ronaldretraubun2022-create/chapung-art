<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaItem extends Model
{
    protected $fillable = [
        'collection_name',
        'file_path',
        'file_type',
        'title',
        'alt_text',
        'sort_order',
        'is_cover',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_cover' => 'boolean',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
