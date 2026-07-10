<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function checkout(array $payload): Order
    {
        $token = (string) Arr::get($payload, 'checkout_token');
        $completedOrderId = session('checkout.completed.'.$token);

        if ($completedOrderId) {
            return Order::with('items')->findOrFail($completedOrderId);
        }

        if (! hash_equals((string) session('checkout.token'), $token)) {
            throw ValidationException::withMessages([
                'checkout_token' => 'Sesi checkout tidak valid. Muat ulang halaman checkout.',
            ]);
        }

        $cartItems = $this->cart->rawItems();

        if ($cartItems === []) {
            throw ValidationException::withMessages([
                'cart' => 'Cart masih kosong.',
            ]);
        }

        $order = DB::transaction(function () use ($payload, $cartItems): Order {
            $artworkIds = array_keys($cartItems);
            $artworks = Artwork::query()
                ->whereKey($artworkIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $lines = [];

            foreach ($cartItems as $key => $item) {
                $artwork = $artworks->get((int) $key);

                if (! $artwork || $artwork->status !== 'available') {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu artwork tidak tersedia untuk checkout.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < 1 || $quantity > (int) $artwork->stock) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Jumlah item melebihi stok terbaru.',
                    ]);
                }

                $price = (float) ($artwork->price ?? 0);
                $lineTotal = $price * $quantity;
                $subtotal += $lineTotal;

                $lines[] = [
                    'artwork' => $artwork,
                    'title' => $artwork->title,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                ];
            }

            $customer = $this->createOrUpdateCustomer($payload);

            $order = Order::create([
                'customer_id' => $customer->id,
                'customer_name' => (string) $payload['customer_name'],
                'customer_email' => (string) $payload['customer_email'],
                'customer_phone' => (string) $payload['customer_phone'],
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'shipping_total' => 0,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $this->orderNotes($payload),
            ]);

            foreach ($lines as $line) {
                /** @var Artwork $artwork */
                $artwork = $line['artwork'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_type' => 'artwork',
                    'product_id' => $artwork->id,
                    'title' => $line['title'],
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                ]);

                $newStock = max(0, (int) $artwork->stock - (int) $line['quantity']);
                $artwork->forceFill([
                    'stock' => $newStock,
                    'status' => $newStock === 0 ? 'sold' : $artwork->status,
                ])->save();
            }

            return $order->fresh(['items']) ?: $order;
        });

        $this->cart->clear();
        session()->forget('checkout.token');
        session()->put('checkout.completed.'.$token, $order->id);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createOrUpdateCustomer(array $payload): Customer
    {
        return Customer::updateOrCreate(
            ['email' => (string) $payload['customer_email']],
            [
                'user_id' => auth()->id(),
                'name' => (string) $payload['customer_name'],
                'phone' => (string) $payload['customer_phone'],
                'whatsapp' => $payload['customer_whatsapp'] ?: $payload['customer_phone'],
                'province' => (string) $payload['province'],
                'city' => (string) $payload['city'],
                'address' => (string) $payload['address'],
                'notes' => $payload['notes'] ?? null,
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function orderNotes(array $payload): ?string
    {
        $parts = array_filter([
            'Alamat: '.$payload['address'].', '.$payload['city'].', '.$payload['province'],
            filled($payload['customer_whatsapp'] ?? null) ? 'WhatsApp: '.$payload['customer_whatsapp'] : null,
            filled($payload['notes'] ?? null) ? 'Catatan: '.$payload['notes'] : null,
        ]);

        return $parts === [] ? null : implode("\n", $parts);
    }
}
