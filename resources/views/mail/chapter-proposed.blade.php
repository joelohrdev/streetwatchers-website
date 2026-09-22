<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New group to review</title>
</head>
<body style="margin:0;padding:24px;background:#f8f7f3;color:#000;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #dbdbdb;">
        <tr>
            <td style="padding:28px 32px;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#616161;">New group to review</p>
                <h1 style="margin:0 0 24px;font-size:20px;">{{ $chapter->name }}</h1>

                <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:14px;margin-bottom:24px;">
                    <tr>
                        <td style="padding:2px 16px 2px 0;color:#616161;">Where</td>
                        <td style="padding:2px 0;">{{ $chapter->city }}, {{ $chapter->country->label() }}</td>
                    </tr>
                    <tr>
                        <td style="padding:2px 16px 2px 0;color:#616161;">Proposed by</td>
                        <td style="padding:2px 0;">{{ $proposer->name }} ({{ $proposer->email }})</td>
                    </tr>
                </table>

                <div style="font-size:15px;line-height:1.6;padding:16px;background:#f8f7f3;white-space:pre-wrap;">{{ $chapter->description }}</div>

                <p style="margin:24px 0 0;font-size:14px;">
                    <a href="{{ $adminUrl }}" style="color:#000;">Review it in the admin panel</a>, or reply to this email to reach {{ $proposer->name }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
