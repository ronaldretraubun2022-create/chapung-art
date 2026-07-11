<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\ArtworkFavorite;
use App\Models\User;

class FavoriteService
{
    public function add(User $user, Artwork $artwork): ArtworkFavorite
    {
        return ArtworkFavorite::firstOrCreate([
            'user_id' => $user->id,
            'artwork_id' => $artwork->id,
        ]);
    }

    public function remove(User $user, Artwork $artwork): void
    {
        ArtworkFavorite::query()
            ->where('user_id', $user->id)
            ->where('artwork_id', $artwork->id)
            ->delete();
    }

    public function isFavorited(User $user, Artwork $artwork): bool
    {
        return ArtworkFavorite::query()
            ->where('user_id', $user->id)
            ->where('artwork_id', $artwork->id)
            ->exists();
    }

    public function count(User $user): int
    {
        return ArtworkFavorite::query()->where('user_id', $user->id)->count();
    }
}
