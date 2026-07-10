<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * @return array<string, mixed>
     */
    public function data(Order $order): array
    {
        $order->ensureInvoiceNumber();
        $order->loadMissing(['customer', 'items']);

        return [
            'site_name' => site_setting('site_name', 'Chapung Art'),
            'site_description' => site_setting('site_description', 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.'),
            'site_email' => site_setting('email', 'info@chapungart.com'),
            'site_phone' => site_setting('phone', site_setting('whatsapp', '')),
            'site_address' => site_setting('address', 'Merauke, Papua Selatan'),
            'order' => $order,
        ];
    }

    public function pdf(Order $order): string
    {
        $data = $this->data($order);
        $lines = $this->pdfLines($data);

        return $this->buildPdf($lines);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function pdfLines(array $data): array
    {
        /** @var Order $order */
        $order = $data['order'];
        $lines = [
            (string) $data['site_name'],
            'Invoice '.$order->invoice_number,
            'Order '.$order->order_number,
            'Issued '.optional($order->invoiced_at)->format('d M Y H:i'),
            '',
            'Billed To',
            $order->customer_name,
            $order->customer_email ?: '-',
            $order->customer_phone ?: '-',
            '',
            'Items',
        ];

        foreach ($order->items as $item) {
            $lines[] = Str::limit($item->title, 56, '');
            $lines[] = 'Qty '.$item->quantity.' x Rp '.number_format((float) $item->price, 0, ',', '.').' = Rp '.number_format((float) $item->total, 0, ',', '.');
        }

        return [
            ...$lines,
            '',
            'Subtotal: Rp '.number_format((float) $order->subtotal, 0, ',', '.'),
            'Discount: Rp '.number_format((float) $order->discount_total, 0, ',', '.'),
            'Shipping: Rp '.number_format((float) $order->shipping_total, 0, ',', '.'),
            'Total: Rp '.number_format((float) $order->grand_total, 0, ',', '.'),
            '',
            'Payment Status: '.Str::headline($order->payment_status),
            'Order Status: '.Str::headline($order->status),
            '',
            (string) $data['site_description'],
            trim((string) $data['site_address']),
            trim((string) $data['site_email'].' '.(string) $data['site_phone']),
        ];
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function buildPdf(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n72 770 Td\n14 TL\n";

        foreach ($lines as $line) {
            foreach ($this->wrapLine($line) as $wrapped) {
                $content .= '('.$this->escapePdfText($wrapped).") Tj\nT*\n";
            }
        }

        $content .= "ET\n";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length ".strlen($content)." >>\nstream\n{$content}endstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= str_pad((string) $offsets[$index], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    /**
     * @return array<int, string>
     */
    private function wrapLine(string $line): array
    {
        if ($line === '') {
            return [''];
        }

        return explode("\n", wordwrap($line, 82, "\n", true));
    }

    private function escapePdfText(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) ?: $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
