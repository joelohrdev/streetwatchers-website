<?php

namespace App\Enums;

enum EventRsvpStatus: string
{
    case Going = 'going';
    case Interested = 'interested';
    // Stored as 'cancelled' in existing RSVP rows, so the value keeps the British spelling.
    case Canceled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Going => 'Going',
            self::Interested => 'Interested',
            self::Canceled => 'Canceled',
        };
    }
}
