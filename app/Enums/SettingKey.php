<?php

namespace App\Enums;

enum SettingKey: string
{
    case NewPhotosRequireReview = 'new_photos_require_review';
    case AnnouncementBanner = 'announcement_banner';

    public function label(): string
    {
        return match ($this) {
            self::NewPhotosRequireReview => 'New photos require review',
            self::AnnouncementBanner => 'Announcement banner',
        };
    }
}
