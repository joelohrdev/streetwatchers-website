<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum ReportStatus: string
{
    use HasOptions;

    case Open = 'open';
    case Reviewed = 'reviewed';
    case Actioned = 'actioned';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Reviewed => 'Reviewed',
            self::Actioned => 'Actioned',
            self::Dismissed => 'Dismissed',
        };
    }
}
