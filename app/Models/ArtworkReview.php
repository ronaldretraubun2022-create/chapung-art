<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ArtworkReview extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'artwork_id',
        'user_id',
        'order_item_id',
        'moderated_by',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'title',
        'body',
        'status',
        'is_verified_purchase',
        'moderated_at',
        'moderation_note',
        'ip_hash',
        'user_agent',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'moderated_at' => 'datetime',
    ];

    public function setTitleAttribute(?string $value): void
    {
        $this->attributes['title'] = filled($value)
            ? Str::limit(strip_tags((string) $value), 120, '')
            : null;
    }

    public function setBodyAttribute(string $value): void
    {
        $this->attributes['body'] = Str::limit(strip_tags((string) HtmlSanitizer::clean($value)), 2000, '');
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function approve(?User $moderator = null, ?string $note = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_APPROVED,
            'moderated_by' => $moderator?->id,
            'moderated_at' => now(),
            'moderation_note' => $note,
        ])->save();
    }

    public function reject(?User $moderator = null, ?string $note = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'moderated_by' => $moderator?->id,
            'moderated_at' => now(),
            'moderation_note' => $note,
        ])->save();
    }
}
