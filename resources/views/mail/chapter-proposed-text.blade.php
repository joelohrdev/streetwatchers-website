New group to review: {{ $chapter->name }}

Where: {{ $chapter->city }}, {{ $chapter->country->label() }}
Proposed by: {{ $proposer->name }} ({{ $proposer->email }})

{{ $chapter->description }}

Review it in the admin panel:
{{ $adminUrl }}

Or reply to this email to reach {{ $proposer->name }}.
