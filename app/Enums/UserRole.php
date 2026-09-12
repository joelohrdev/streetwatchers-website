<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Correspondent = 'correspondent';
    case ChapterAdmin = 'chapteradmin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Correspondent => 'Correspondent',
            self::ChapterAdmin => 'Chapter Admin',
            self::Member => 'Member',
        };
    }
}
