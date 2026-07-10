<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(CartService $cart): View
    {
        return view('cart.index', [
            'cart' => $cart->summary(),
        ]);
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'artwork_id' => ['required', 'integer', 'exists:artworks,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $artwork = Artwork::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'artist_name', 'thumbnail', 'price', 'stock', 'status'])
            ->with('artist:id,name')
            ->findOrFail($validated['artwork_id']);

        $cart->addArtwork($artwork, (int) ($validated['quantity'] ?? 1));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Artwork ditambahkan ke cart.',
        ]);
    }

    public function update(Request $request, int $artwork, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart->updateArtwork($artwork, (int) $validated['quantity']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Quantity cart diperbarui.',
        ]);
    }

    public function destroy(int $artwork, CartService $cart): RedirectResponse
    {
        $cart->removeArtwork($artwork);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Item dihapus dari cart.',
        ]);
    }
}
