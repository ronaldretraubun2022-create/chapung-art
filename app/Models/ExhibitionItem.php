<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionItem extends Model
{
    protected $fillable = [
        'exhibition_id',
        'item_type',
        'item_id',
        'sort_order',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function getItemTitleAttribute(): string
    {
        return match ($this->item_type) {
            'artwork' => Artwork::query()->whereKey($this->item_id)->value('title') ?: 'Artwork #'.$this->item_id,
            'photography' => Photography::query()->whereKey($this->item_id)->value('title') ?: 'Photography #'.$this->item_id,
            default => 'Item #'.$this->item_id,
        };
    }
}
