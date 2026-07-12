<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

trait HasLocalizedNavigation
{
    public static function getNavigationLabel(): string
    {
        return static::localizedNavigationText(parent::getNavigationLabel(), 'resources');
    }

    public static function getNavigationGroup(): ?string
    {
        $group = parent::getNavigationGroup();

        return filled($group) ? static::localizedNavigationText((string) $group, 'groups') : null;
    }

    private static function localizedNavigationText(string $fallback, string $section): string
    {
        $key = 'admin.navigation.'.$section.'.'.Str::of($fallback)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');

        $translated = __((string) $key);

        return $translated === (string) $key ? $fallback : $translated;
    }
}
