<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $ordersQuery = $this->customerOrdersQuery($user->id, $user->email);

        return view('dashboard', [
            'summary' => [
                'total' => (clone $ordersQuery)->count(),
                'active' => (clone $ordersQuery)->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'paid' => (clone $ordersQuery)->where('payment_status', 'paid')->count(),
                'grand_total' => (float) (clone $ordersQuery)->sum('grand_total'),
            ],
            'recentOrders' => (clone $ordersQuery)
                ->withCount('items')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $orders = $this->customerOrdersQuery($user->id, $user->email)
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order, Request $request, OrderAccessService $access): View
    {
        $access->authorize($order, $request->user());

        $order->loadMissing([
            'items',
            'payments' => fn ($query) => $query->latest(),
            'shipments' => fn ($query) => $query->latest(),
            'statusHistories.changedBy:id,name,email',
        ]);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    private function customerOrdersQuery(int $userId, string $email)
    {
        return Order::query()
            ->where(function ($query) use ($userId, $email): void {
                $query->whereHas('customer', fn ($customer) => $customer->where('user_id', $userId))
                    ->orWhere('customer_email', $email);
            });
    }
}
