<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $payload['subject'] }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    @include('mail.partials.brand-header')

    <h1 style="font-size: 20px; margin-bottom: 8px;">Chapung Art Contact Inquiry</h1>
    <p style="margin: 0 0 16px; color: #4b5563;">Mailbox: {{ $departmentLabel }}</p>

    <table role="presentation" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        <tr>
            <td style="font-weight: bold; width: 140px; border: 1px solid #e5e7eb;">Name</td>
            <td style="border: 1px solid #e5e7eb;">{{ $payload['name'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: 1px solid #e5e7eb;">Email</td>
            <td style="border: 1px solid #e5e7eb;">{{ $payload['email'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: 1px solid #e5e7eb;">Subject</td>
            <td style="border: 1px solid #e5e7eb;">{{ $payload['subject'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: 1px solid #e5e7eb; vertical-align: top;">Message</td>
            <td style="border: 1px solid #e5e7eb; white-space: pre-line;">{{ $payload['message'] }}</td>
        </tr>
    </table>
</body>
</html>
