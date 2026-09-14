<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum CritiqueGroupStatus: string
{
    use HasOptions;

    case Forming = 'forming';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Forming => 'Forming',
            self::Active => 'Active',
            self::Closed => 'Closed',
        };
    }
}
