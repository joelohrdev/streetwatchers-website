<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case CORRESPONDENT = 'correspondent';
    case CHAPTERADMIN = 'chapteradmin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::CORRESPONDENT => 'Correspondent',
            self::CHAPTERADMIN => 'Chapter Admin',
            self::MEMBER => 'Member',
        };
    }
}
