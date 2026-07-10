<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $order->invoice_number }} | {{ $site_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #111 !important; }
        }
    </style>
</head>
<body class="bg-zinc-100 text-zinc-950 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="no-print mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('checkout.success', $order->order_number) }}" class="text-sm font-bold text-zinc-600 hover:text-zinc-950">Back to order</a>
            <a href="{{ route('invoice.download', $order) }}" class="rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Download PDF</a>
        </div>

        <article class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-zinc-200 sm:p-10">
            <header class="flex flex-col gap-8 border-b border-zinc-200 pb-8 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-700">Invoice</p>
                    <h1 class="mt-3 text-3xl font-black uppercase tracking-tight text-zinc-950">{{ $order->invoice_number }}</h1>
                    <p class="mt-2 text-sm text-zinc-500">Order {{ $order->order_number }}</p>
                    <p class="mt-1 text-sm text-zinc-500">Issued {{ optional($order->invoiced_at)->format('d M Y H:i') }}</p>
                </div>

                <div class="sm:text-right">
                    <h2 class="text-2xl font-black uppercase tracking-[0.22em] text-zinc-950">{{ $site_name }}</h2>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-zinc-500">{{ $site_description }}</p>
                    <p class="mt-3 text-sm leading-6 text-zinc-600">{{ $site_address }}</p>
                    <p class="text-sm text-zinc-600">{{ $site_email }}</p>
                    @if ($site_phone)
                        <p class="text-sm text-zinc-600">{{ $site_phone }}</p>
                    @endif
                </div>
            </header>

            <section class="grid gap-6 border-b border-zinc-200 py-8 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Billed To</p>
                    <h3 class="mt-3 text-lg font-black text-zinc-950">{{ $order->customer_name }}</h3>
                    <p class="mt-1 text-sm text-zinc-600">{{ $order->customer_email ?: '-' }}</p>
                    <p class="text-sm text-zinc-600">{{ $order->customer_phone ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">Status</p>
                    <div class="mt-3 grid gap-2 text-sm text-zinc-600">
                        <p><span class="font-bold text-zinc-950">Order:</span> {{ ucfirst($order->status) }}</p>
                        <p><span class="font-bold text-zinc-950">Payment:</span> {{ ucfirst($order->payment_status) }}</p>
                    </div>
                </div>
            </section>

            <section class="py-8">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b border-zinc-200 text-xs uppercase tracking-[0.14em] text-zinc-500">
                            <tr>
                                <th class="py-3 pr-4">Item</th>
                                <th class="py-3 pr-4 text-right">Price</th>
                                <th class="py-3 pr-4 text-right">Qty</th>
                                <th class="py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-4 pr-4 font-bold text-zinc-950">{{ $item->title }}</td>
                                    <td class="py-4 pr-4 text-right text-zinc-600">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="py-4 pr-4 text-right text-zinc-600">{{ $item->quantity }}</td>
                                    <td class="py-4 text-right font-bold text-zinc-950">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="grid gap-8 border-t border-zinc-200 pt-8 sm:grid-cols-[1fr_320px]">
                <p class="text-sm leading-7 text-zinc-500">Terima kasih telah mendukung karya seni dan dokumentasi budaya Papua Selatan bersama Chapung Art.</p>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 text-zinc-600"><span>Subtotal</span><strong>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-600"><span>Discount</span><strong>Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-600"><span>Shipping</span><strong>Rp {{ number_format((float) $order->shipping_total, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 border-t border-zinc-200 pt-3 text-lg font-black text-zinc-950"><span>Total</span><strong>Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong></div>
                </div>
            </footer>
        </article>
    </main>
</body>
</html>
