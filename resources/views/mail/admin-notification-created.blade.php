<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $notification->title }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #18181b; line-height: 1.6;">
    <h1 style="margin-bottom: 8px;">{{ $notification->title }}</h1>
    <p style="margin-top: 0;">{{ $notification->message }}</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">Type</th>
            <td style="border: 1px solid #e4e4e7;">{{ $notification->type }}</td>
        </tr>
        <tr>
            <th align="left" style="background: #f4f4f5; border: 1px solid #e4e4e7;">URL</th>
            <td style="border: 1px solid #e4e4e7;">
                @if ($notification->url)
                    <a href="{{ $notification->url }}">{{ $notification->url }}</a>
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <p style="color: #71717a; font-size: 12px; margin-top: 24px;">Chapung Art system notification.</p>
</body>
</html>
