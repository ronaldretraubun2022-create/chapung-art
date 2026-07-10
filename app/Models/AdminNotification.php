<?php

namespace App\Models;

use App\Services\InternalNotificationMailService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (AdminNotification $notification): void {
            app(InternalNotificationMailService::class)->notifyAdmins($notification);
        });
    }

    public function markAsRead(): void
    {
        if ($this->read_at) {
            return;
        }

        $this->forceFill(['read_at' => now()])->save();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
