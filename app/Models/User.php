<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    private const PANEL_ROLES = [
        'Super Admin',
        'Pengelola Karya',
        'Pengelola Transaksi',
        'Pengelola Konten',
        'Operator Viewer',
        'Administrator',
        'Curator',
        'Artist',
        'Photographer',
        'Journalist',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locale' => 'string',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->permissionTablesExist()) {
            return $this->isConfiguredAdmin();
        }

        if ($this->hasRole('Customer')) {
            return false;
        }

        if ($this->hasAnyRole(self::PANEL_ROLES)) {
            return $this->isConfiguredAdmin();
        }

        return $this->roles()->doesntExist() && $this->isConfiguredAdmin();
    }

    public function isSuperAdmin(): bool
    {
        return $this->permissionTablesExist() && $this->hasRole('Super Admin');
    }

    public function isLegacyAdminWithoutRoles(): bool
    {
        return $this->permissionTablesExist()
            && $this->roles()->doesntExist()
            && $this->isConfiguredAdmin();
    }

    public function isConfiguredAdmin(): bool
    {
        $adminEmails = collect(config('chapung.admin_emails', []));

        if ($adminEmails->isEmpty()) {
            return ! app()->isProduction();
        }

        return $adminEmails->contains(mb_strtolower($this->email));
    }

    private function permissionTablesExist(): bool
    {
        return Schema::hasTable(config('permission.table_names.roles', 'roles'))
            && Schema::hasTable(config('permission.table_names.model_has_roles', 'model_has_roles'));
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    public function artworkReviews(): HasMany
    {
        return $this->hasMany(ArtworkReview::class);
    }

    public function artworkFavorites(): HasMany
    {
        return $this->hasMany(ArtworkFavorite::class);
    }

    public function favoriteArtworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_favorites')->withTimestamps();
    }
}
