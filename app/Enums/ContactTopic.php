<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum ContactTopic: string
{
    use HasOptions;

    case General = 'general';
    case Groups = 'groups';
    case Support = 'support';
    case Press = 'press';
    case Partnerships = 'partnerships';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General question',
            self::Groups => 'Starting or joining a group',
            self::Support => 'Account or technical help',
            self::Press => 'Press and media',
            self::Partnerships => 'Partnerships',
        };
    }
}
