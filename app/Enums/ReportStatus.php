<?php

namespace App\Enums;

enum ReportStatus: string
{
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
