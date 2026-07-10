<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

function invoiceOrder(?User $user = null, array $overrides = []): Order
{
    $customer = Customer::create([
        'user_id' => $user?->id,
        'name' => 'Invoice Customer',
        'email' => 'invoice@example.com',
        'phone' => '081234567890',
        'whatsapp' => '081234567890',
        'province' => 'Papua Selatan',
        'city' => 'Merauke',
        'address' => 'Jl. Invoice No. 1',
        'is_active' => true,
    ]);

    $order = Order::create(array_merge([
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => $customer->phone,
        'subtotal' => 250000,
        'discount_total' => 0,
        'shipping_total' => 0,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ], $overrides));

    OrderItem::create([
        'order_id' => $order->id,
        'product_type' => 'artwork',
        'product_id' => 1,
        'title' => 'Invoice Artwork',
        'price' => 250000,
        'quantity' => 1,
    ]);

    return $order->fresh(['customer', 'items']);
}

test('orders receive unique invoice numbers', function () {
    $first = invoiceOrder();
    $second = invoiceOrder(null, ['customer_email' => 'second@example.com']);

    expect($first->invoice_number)->toMatch('/^INV-\d{8}-\d{5}$/')
        ->and($second->invoice_number)->toMatch('/^INV-\d{8}-\d{5}$/')
        ->and($first->invoice_number)->not->toBe($second->invoice_number)
        ->and($first->invoiced_at)->not->toBeNull();
});

test('guest cannot open invoice html or pdf', function () {
    $order = invoiceOrder();

    $this->get(route('invoice.show', $order))->assertRedirect('/login');
    $this->get(route('invoice.download', $order))->assertRedirect('/login');
});

test('customer owner can view branded html invoice', function () {
    $user = User::factory()->create();
    $order = invoiceOrder($user);

    $this->actingAs($user)
        ->get(route('invoice.show', $order))
        ->assertOk()
        ->assertSee('Chapung Art')
        ->assertSee($order->invoice_number)
        ->assertSee($order->order_number)
        ->assertSee('Invoice Artwork')
        ->assertSee('Download PDF');
});

test('other authenticated customer cannot access invoice', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = invoiceOrder($owner);

    $this->actingAs($other)
        ->get(route('invoice.show', $order))
        ->assertForbidden();

    $this->actingAs($other)
        ->get(route('invoice.download', $order))
        ->assertForbidden();
});

test('customer owner can download pdf invoice safely', function () {
    $user = User::factory()->create();
    $order = invoiceOrder($user);

    $response = $this->actingAs($user)->get(route('invoice.download', $order));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Content-Disposition'))->toContain($order->invoice_number.'.pdf')
        ->and($response->getContent())->toStartWith('%PDF-1.4')
        ->and($response->getContent())->toContain($order->invoice_number)
        ->and($response->getContent())->toContain('Chapung Art');
});

test('existing order without invoice gets invoice number lazily', function () {
    $user = User::factory()->create();
    $order = invoiceOrder($user);
    $order->forceFill(['invoice_number' => null, 'invoiced_at' => null])->saveQuietly();

    $this->actingAs($user)->get(route('invoice.show', $order))->assertOk();

    expect($order->fresh()->invoice_number)->toMatch('/^INV-\d{8}-\d{5}$/')
        ->and($order->fresh()->invoiced_at)->not->toBeNull();
});
