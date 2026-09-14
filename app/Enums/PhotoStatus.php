<?php

namespace App\Enums;

enum PhotoStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Flagged = 'flagged';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Published => 'Published',
            self::Flagged => 'Flagged',
            self::Removed => 'Removed',
        };
    }
}
