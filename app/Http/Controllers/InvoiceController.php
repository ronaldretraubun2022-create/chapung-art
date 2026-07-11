<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\OrderAccessService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Order $order, InvoiceService $invoice, OrderAccessService $access): View
    {
        $access->authorize($order, auth()->user());

        return view('invoice.show', $invoice->data($order));
    }

    public function download(Order $order, InvoiceService $invoice, OrderAccessService $access): Response
    {
        $access->authorize($order, auth()->user());

        $order->ensureInvoiceNumber();
        $filename = $order->invoice_number.'.pdf';

        return response($invoice->pdf($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
