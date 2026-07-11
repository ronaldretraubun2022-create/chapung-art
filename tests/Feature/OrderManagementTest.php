<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;

function managedOrder(User $user, array $overrides = []): Order
{
    $customer = Customer::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '081344001427',
        'whatsapp' => '081344001427',
        'province' => 'Papua Selatan',
        'city' => 'Merauke',
        'address' => 'Jl. Sesate No. 242',
        'is_active' => true,
    ]);

    $order = Order::create(array_merge([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => $customer->phone,
        'subtotal' => 350000,
        'discount_total' => 0,
        'shipping_total' => 25000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'notes' => 'Alamat: Merauke',
    ], $overrides));

    OrderItem::create([
        'order_id' => $order->id,
        'product_type' => 'artwork',
        'product_id' => 1,
        'title' => 'Managed Artwork',
        'price' => 350000,
        'quantity' => 1,
    ]);

    return $order->fresh(['customer', 'items', 'statusHistories']);
}

test('customer dashboard shows order management summary', function () {
    $user = User::factory()->create(['email' => 'collector@example.com']);
    $order = managedOrder($user, ['payment_status' => 'paid']);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Collector Dashboard')
        ->assertSee('Recent Orders')
        ->assertSee($order->order_number)
        ->assertSee('Rp 375.000');
});

test('customer order history only shows owned orders', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $other = User::factory()->create(['email' => 'other@example.com']);
    $ownedOrder = managedOrder($owner);
    $otherOrder = managedOrder($other, ['customer_name' => 'Other Customer']);

    $this->actingAs($owner)
        ->withSession(['locale' => 'en'])
        ->get(route('orders.index'))
        ->assertOk()
        ->assertSee($ownedOrder->order_number)
        ->assertDontSee($otherOrder->order_number)
        ->assertDontSee('Other Customer');
});

test('customer can view order detail with invoice status history payment and shipment', function () {
    $user = User::factory()->create(['email' => 'detail@example.com']);
    $order = managedOrder($user);

    Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'manual_transfer',
        'amount' => $order->grand_total,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    Shipment::create([
        'order_id' => $order->id,
        'courier' => 'Kurir Merauke',
        'tracking_number' => 'MRK-001',
        'destination' => 'Merauke',
        'shipping_cost' => 25000,
        'status' => 'shipped',
        'shipped_at' => now(),
    ]);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Download PDF')
        ->assertSee('Status Timeline')
        ->assertSee('Managed Artwork')
        ->assertSee('Manual Transfer')
        ->assertSee('Kurir Merauke')
        ->assertSee('MRK-001');

    expect(OrderStatusHistory::query()->where('order_id', $order->id)->count())->toBeGreaterThanOrEqual(3)
        ->and($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->status)->toBe('shipped');
});

test('other authenticated user cannot view customer order detail', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $other = User::factory()->create(['email' => 'intruder@example.com']);
    $order = managedOrder($owner);

    $this->actingAs($other)
        ->get(route('orders.show', $order))
        ->assertForbidden();
});

test('order status updates create history records', function () {
    $user = User::factory()->create(['email' => 'history@example.com']);
    $order = managedOrder($user);

    $order->forceFill(['status' => 'processing'])->save();
    $order->forceFill(['payment_status' => 'paid'])->save();

    expect(OrderStatusHistory::query()->where('order_id', $order->id)->count())->toBe(3)
        ->and(OrderStatusHistory::query()->where('order_id', $order->id)->latest()->first()->payment_status_to)->toBe('paid');
});
