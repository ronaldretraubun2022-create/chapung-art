<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class InvoiceController extends Controller
{
    public function show(Order $order, InvoiceService $invoice): View
    {
        $this->authorizeInvoice($order);

        return view('invoice.show', $invoice->data($order));
    }

    public function download(Order $order, InvoiceService $invoice): Response
    {
        $this->authorizeInvoice($order);

        $order->ensureInvoiceNumber();
        $filename = $order->invoice_number.'.pdf';

        return response($invoice->pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeInvoice(Order $order): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user, 403);

        $order->loadMissing('customer');

        if ($order->customer?->user_id === $user->id) {
            return;
        }

        try {
            if ($user->isSuperAdmin() || $user->isLegacyAdminWithoutRoles() || Gate::forUser($user)->allows('view', $order)) {
                return;
            }
        } catch (Throwable) {
            // Fall through to the forbidden response.
        }

        abort(403);
    }
}
