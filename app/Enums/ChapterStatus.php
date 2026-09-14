<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum ChapterStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}
