<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Payment Notification</title>
</head>
<body style="font-family: Arial, sans-serif; color: #18181b; line-height: 1.6;">
    @include('mail.partials.brand-header')

    <h1 style="margin-bottom: 8px;">Payment baru</h1>
    <p style="margin-top: 0;">Tim finance menerima notifikasi pembayaran baru dari Chapung Art.</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">Order</th>
            <td style="border: 1px solid #e4e4e7;">{{ $payment->order?->order_number ?: '#'.$payment->order_id }}</td>
        </tr>
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">Amount</th>
            <td style="border: 1px solid #e4e4e7;">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">Method</th>
            <td style="border: 1px solid #e4e4e7;">{{ $payment->payment_method }}</td>
        </tr>
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">Status</th>
            <td style="border: 1px solid #e4e4e7;">{{ $payment->status }}</td>
        </tr>
    </table>

    <p style="color: #71717a; font-size: 12px; margin-top: 24px;">Periksa dashboard admin untuk validasi dan rekonsiliasi pembayaran.</p>
</body>
</html>
