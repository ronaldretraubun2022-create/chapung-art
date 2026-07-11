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

        $this->cart->setShippingEstimate((string) $payload['shipping_area']);

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

            $adjustments = $this->cart->adjustmentsFor($subtotal);
            $customer = $this->createOrUpdateCustomer($payload);

            $order = Order::create([
                'customer_id' => $customer->id,
                'customer_name' => (string) $payload['customer_name'],
                'customer_email' => (string) $payload['customer_email'],
                'customer_phone' => (string) $payload['customer_phone'],
                'subtotal' => $subtotal,
                'discount_total' => (float) $adjustments['discount_total'],
                'shipping_total' => (float) $adjustments['shipping_total'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $this->orderNotes($payload, $adjustments),
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
                'address' => $this->fullAddress($payload),
                'notes' => $this->customerNotes($payload),
                'is_active' => true,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function orderNotes(array $payload, array $adjustments): ?string
    {
        $parts = array_filter([
            'Alamat: '.$this->fullAddress($payload),
            'Pengiriman: '.$this->shippingLabel($payload, $adjustments),
            filled($payload['shipping_notes'] ?? null) ? 'Catatan pengiriman: '.$payload['shipping_notes'] : null,
            'Pembayaran: '.$this->paymentLabel((string) $payload['payment_method']),
            filled($adjustments['coupon_code'] ?? null) ? 'Kupon: '.$adjustments['coupon_code'].' / '.$adjustments['coupon_label'] : null,
            filled($payload['customer_whatsapp'] ?? null) ? 'WhatsApp: '.$payload['customer_whatsapp'] : null,
            filled($payload['notes'] ?? null) ? 'Catatan: '.$payload['notes'] : null,
        ]);

        return $parts === [] ? null : implode("\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function fullAddress(array $payload): string
    {
        return collect([
            $payload['address'] ?? null,
            $payload['district'] ?? null,
            $payload['city'] ?? null,
            $payload['province'] ?? null,
            $payload['postal_code'] ?? null,
        ])->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->implode(', ');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function customerNotes(array $payload): ?string
    {
        $notes = array_filter([
            filled($payload['district'] ?? null) ? 'Distrik: '.$payload['district'] : null,
            filled($payload['postal_code'] ?? null) ? 'Kode pos: '.$payload['postal_code'] : null,
            filled($payload['notes'] ?? null) ? (string) $payload['notes'] : null,
        ]);

        return $notes === [] ? null : implode("\n", $notes);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $adjustments
     */
    private function shippingLabel(array $payload, array $adjustments): string
    {
        $area = (string) ($payload['shipping_area'] ?? '');
        $label = (string) ($adjustments['shipping_label'] ?? config('chapung.cart.shipping_estimates.'.$area.'.label', $area));
        $amount = (float) ($adjustments['shipping_total'] ?? 0);

        return trim($label.' / Rp '.number_format($amount, 0, ',', '.'));
    }

    private function paymentLabel(string $method): string
    {
        return (string) config('chapung.checkout.payment_methods.'.$method.'.label', $method);
    }
}
