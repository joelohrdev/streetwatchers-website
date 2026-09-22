<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your group has been deactivated</title>
</head>
<body style="margin:0;padding:24px;background:#f8f7f3;color:#000;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #dbdbdb;">
        <tr>
            <td style="padding:28px 32px;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#616161;">Group deactivated</p>
                <h1 style="margin:0 0 24px;font-size:20px;">{{ $chapter->name }}</h1>

                <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                    {{ $chapter->name }} has been deactivated. It no longer appears in the group directory, and its page and meetups can't be viewed. Members keep their StreetWatchers accounts.
                </p>
                <p style="margin:0;font-size:15px;line-height:1.6;">
                    If you have questions or think this is a mistake, <a href="{{ $contactUrl }}" style="color:#000;">get in touch</a>.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
