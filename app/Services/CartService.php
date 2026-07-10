<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const GUEST_KEY = 'cart.items';

    public function addArtwork(Artwork $artwork, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);
        $this->ensurePurchasable($artwork);

        $items = $this->rawItems();
        $key = (string) $artwork->id;
        $existingQuantity = (int) ($items[$key]['quantity'] ?? 0);
        $newQuantity = $existingQuantity + $quantity;

        if ($newQuantity > (int) $artwork->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah item melebihi stok yang tersedia.',
            ]);
        }

        $items[$key] = $this->cartLine($artwork, $newQuantity);

        $this->putRawItems($items);
    }

    public function updateArtwork(int $artworkId, int $quantity): void
    {
        $items = $this->rawItems();
        $key = (string) $artworkId;

        if (! array_key_exists($key, $items)) {
            throw ValidationException::withMessages([
                'cart' => 'Artwork tidak ditemukan di cart.',
            ]);
        }

        $artwork = Artwork::query()->findOrFail($artworkId);
        $this->ensurePurchasable($artwork);

        $quantity = max(1, $quantity);

        if ($quantity > (int) $artwork->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah item melebihi stok yang tersedia.',
            ]);
        }

        $items[$key] = $this->cartLine($artwork, $quantity);

        $this->putRawItems($items);
    }

    public function removeArtwork(int $artworkId): void
    {
        $items = $this->rawItems();
        unset($items[(string) $artworkId]);

        $this->putRawItems($items);
    }

    public function clear(): void
    {
        session()->forget($this->currentKey());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $items = $this->rawItems();

        if ($items === []) {
            return [];
        }

        $artworks = Artwork::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'artist_name', 'thumbnail', 'price', 'stock', 'status'])
            ->with('artist:id,name')
            ->whereKey(array_keys($items))
            ->get()
            ->keyBy('id');

        $hydrated = [];

        foreach ($items as $key => $item) {
            $artwork = $artworks->get((int) $key);

            if (! $artwork) {
                continue;
            }

            $quantity = min(max(1, (int) ($item['quantity'] ?? 1)), max(1, (int) $artwork->stock));
            $hydrated[$key] = $this->cartLine($artwork, $quantity);
        }

        if ($hydrated !== $items) {
            $this->putRawItems($hydrated);
        }

        return array_values($hydrated);
    }

    public function count(): int
    {
        return collect($this->items())->sum(fn (array $item): int => (int) $item['quantity']);
    }

    public function subtotal(): float
    {
        return collect($this->items())->sum(fn (array $item): float => (float) $item['line_total']);
    }

    public function total(): float
    {
        return $this->subtotal();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, count: int, subtotal: float, total: float}
     */
    public function summary(): array
    {
        $items = $this->items();
        $subtotal = collect($items)->sum(fn (array $item): float => (float) $item['line_total']);

        return [
            'items' => $items,
            'count' => collect($items)->sum(fn (array $item): int => (int) $item['quantity']),
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    public function mergeAfterLogin(User $user): void
    {
        $guestItems = session()->get(self::GUEST_KEY, []);
        $userKey = $this->userKey((int) $user->id);
        $userItems = session()->get($userKey, []);

        if (! is_array($guestItems) || $guestItems === []) {
            return;
        }

        $merged = $this->mergeRawItems(is_array($userItems) ? $userItems : [], $guestItems);

        session()->put($userKey, $merged);
        session()->forget(self::GUEST_KEY);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function rawItems(): array
    {
        $items = session()->get($this->currentKey(), []);

        return is_array($items) ? $items : [];
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     */
    public function putRawItems(array $items): void
    {
        session()->put($this->currentKey(), $items);
    }

    private function ensurePurchasable(Artwork $artwork): void
    {
        if ($artwork->status !== 'available') {
            throw ValidationException::withMessages([
                'artwork' => 'Artwork ini belum tersedia untuk dibeli.',
            ]);
        }

        if ((int) $artwork->stock < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok artwork tidak tersedia.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function cartLine(Artwork $artwork, int $quantity): array
    {
        $price = (float) ($artwork->price ?? 0);

        return [
            'type' => 'artwork',
            'artwork_id' => (int) $artwork->id,
            'title' => $artwork->title,
            'slug' => $artwork->slug,
            'artist_name' => $artwork->artist?->name ?: $artwork->artist_name,
            'thumbnail' => $artwork->thumbnail,
            'price' => $price,
            'quantity' => $quantity,
            'stock' => (int) $artwork->stock,
            'status' => $artwork->status,
            'line_total' => $price * $quantity,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $baseItems
     * @param  array<string, array<string, mixed>>  $incomingItems
     * @return array<string, array<string, mixed>>
     */
    private function mergeRawItems(array $baseItems, array $incomingItems): array
    {
        $artworks = Artwork::query()
            ->whereKey(array_unique([...array_keys($baseItems), ...array_keys($incomingItems)]))
            ->get()
            ->keyBy('id');

        return Collection::make($baseItems)
            ->mergeRecursive($incomingItems)
            ->map(function (mixed $item, string $key) use ($baseItems, $incomingItems, $artworks): ?array {
                $artwork = $artworks->get((int) $key);

                if (! $artwork || $artwork->status !== 'available' || (int) $artwork->stock < 1) {
                    return null;
                }

                $baseQuantity = (int) ($baseItems[$key]['quantity'] ?? 0);
                $incomingQuantity = (int) ($incomingItems[$key]['quantity'] ?? 0);
                $quantity = min($baseQuantity + $incomingQuantity, (int) $artwork->stock);

                return $this->cartLine($artwork, max(1, $quantity));
            })
            ->filter()
            ->all();
    }

    private function currentKey(): string
    {
        $userId = auth()->id();

        return $userId ? $this->userKey((int) $userId) : self::GUEST_KEY;
    }

    private function userKey(int $userId): string
    {
        return 'cart.users.'.$userId.'.items';
    }
}
