<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum UserStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Suspended = 'suspended';
    case Banned = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Banned => 'Banned',
        };
    }

    /**
     * The message shown to a user who tries to sign in while blocked.
     */
    public function loginMessage(): string
    {
        return match ($this) {
            self::Active => '',
            self::Suspended => 'This account has been suspended. Contact support if you believe this is a mistake.',
            self::Banned => 'This account has been banned from StreetWatchers.',
        };
    }
}
