<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * @var array<int, string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
    ];

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): ?ActivityLog
    {
        if ($subject instanceof ActivityLog) {
            return null;
        }

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'log_name' => 'admin',
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => self::sanitize($properties),
            'ip_hash' => self::currentIpHash(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function sanitize(array $attributes): array
    {
        return collect($attributes)
            ->reject(fn (mixed $value, string $key): bool => self::isSensitiveKey($key))
            ->map(function (mixed $value): mixed {
                if (is_array($value)) {
                    return self::sanitize($value);
                }

                return $value;
            })
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function modelChanges(Model $model): array
    {
        return [
            'old' => self::sanitize(Arr::only($model->getOriginal(), array_keys($model->getChanges()))),
            'attributes' => self::sanitize($model->getChanges()),
        ];
    }

    private static function currentIpHash(): ?string
    {
        $ip = request()?->ip();

        if (blank($ip)) {
            return null;
        }

        return Hash::make($ip);
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', '.'], '_')->toString();

        return collect(self::SENSITIVE_KEYS)
            ->contains(fn (string $sensitiveKey): bool => $normalized === $sensitiveKey || Str::contains($normalized, $sensitiveKey));
    }
}
