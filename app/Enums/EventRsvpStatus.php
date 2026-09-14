<?php

namespace App\Enums;

enum EventRsvpStatus: string
{
    case Going = 'going';
    case Interested = 'interested';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Going => 'Going',
            self::Interested => 'Interested',
            self::Cancelled => 'Cancelled',
        };
    }
}
