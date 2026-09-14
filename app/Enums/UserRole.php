<?php

namespace App\Enums;

enum UserRole: string
{
    case Member = 'member';
    case ChapterAdmin = 'chapter_admin';
    case Correspondent = 'correspondent';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Member',
            self::ChapterAdmin => 'Chapter Admin',
            self::Correspondent => 'Correspondent',
            self::SuperAdmin => 'Super Admin',
        };
    }
}
