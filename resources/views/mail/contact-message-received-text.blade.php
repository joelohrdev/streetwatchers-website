New contact message: {{ $contactMessage->topic->label() }}

From: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
Sent: {{ $contactMessage->created_at?->toDayDateTimeString() }}

{{ $contactMessage->message }}

Reply to this email to answer {{ $contactMessage->name }} directly, or view it in the admin panel:
{{ $adminUrl }}
