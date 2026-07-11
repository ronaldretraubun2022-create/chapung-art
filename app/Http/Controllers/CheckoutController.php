<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(CartService $cart): View|RedirectResponse
    {
        $summary = $cart->summary();

        if ($summary['items'] === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Cart masih kosong.',
            ]);
        }

        $token = session('checkout.token');

        if (! is_string($token) || blank($token)) {
            $token = (string) Str::uuid();
            session()->put('checkout.token', $token);
        }

        return view('checkout.create', [
            'cart' => $summary,
            'checkoutToken' => $token,
            'paymentMethods' => config('chapung.checkout.payment_methods', []),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->checkout($request->validated());

        return redirect()->route('checkout.success', $order->order_number)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Order berhasil dibuat.',
            ]);
    }

    public function success(string $orderNumber): View
    {
        $order = Order::query()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('checkout.success', [
            'order' => $order,
        ]);
    }
}
