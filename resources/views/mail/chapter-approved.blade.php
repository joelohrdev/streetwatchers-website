<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your group is live</title>
</head>
<body style="margin:0;padding:24px;background:#f8f7f3;color:#000;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #dbdbdb;">
        <tr>
            <td style="padding:28px 32px;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#616161;">Your group is live</p>
                <h1 style="margin:0 0 24px;font-size:20px;">{{ $chapter->name }}</h1>

                <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                    We've approved {{ $chapter->name }}. It's now in the group directory, so photographers in {{ $chapter->city }} can find it and join.
                </p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;">
                    A good next step is to plan your first meetup. Once people join, you can make some of them organizers too, from the group's members page.
                </p>

                <p style="margin:0 0 12px;font-size:14px;">
                    <a href="{{ $planMeetupUrl }}" style="display:inline-block;padding:12px 20px;background:#000;color:#fff;text-decoration:none;font-weight:600;">Plan a meetup</a>
                </p>
                <p style="margin:0;font-size:14px;">
                    <a href="{{ $groupUrl }}" style="color:#000;">See your group page</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
