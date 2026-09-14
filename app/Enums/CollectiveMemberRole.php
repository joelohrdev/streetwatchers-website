<?php

namespace App\Enums;

enum CollectiveMemberRole: string
{
    case Founder = 'founder';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Founder => 'Founder',
            self::Member => 'Member',
        };
    }
}
