<?php

namespace App\Enums;

enum AuditAction: string
{
    case ChapterApproved = 'chapter_approved';
    case ChapterDeactivated = 'chapter_deactivated';
    case ChapterAdminAdded = 'chapter_admin_added';
    case ChapterAdminRemoved = 'chapter_admin_removed';
    case ReportStatusUpdated = 'report_status_updated';
    case PhotoStatusUpdated = 'photo_status_updated';
    case UserStatusUpdated = 'user_status_updated';
    case CollectiveVerified = 'collective_verified';
    case CollectiveUnverified = 'collective_unverified';
    case CollectiveRemoved = 'collective_removed';
    case CorrespondentGranted = 'correspondent_granted';
    case CorrespondentRevoked = 'correspondent_revoked';
    case ArticleUnpublished = 'article_unpublished';
    // Critique groups were removed, but past audit entries for them must still display.
    case CritiqueGroupDissolved = 'critique_group_dissolved';
    case CritiqueGroupMemberRemoved = 'critique_group_member_removed';
    case CritiqueGroupMemberMoved = 'critique_group_member_moved';
    case SettingsUpdated = 'settings_updated';
    case TagMerged = 'tag_merged';
    case TagDeleted = 'tag_deleted';
    case ContactMessageDeleted = 'contact_message_deleted';

    public function label(): string
    {
        return match ($this) {
            self::ChapterApproved => 'Chapter approved',
            self::ChapterDeactivated => 'Chapter deactivated',
            self::ChapterAdminAdded => 'Chapter admin added',
            self::ChapterAdminRemoved => 'Chapter admin removed',
            self::ReportStatusUpdated => 'Report status updated',
            self::PhotoStatusUpdated => 'Photo status updated',
            self::UserStatusUpdated => 'User status updated',
            self::CollectiveVerified => 'Collective verified',
            self::CollectiveUnverified => 'Collective unverified',
            self::CollectiveRemoved => 'Collective removed',
            self::CorrespondentGranted => 'Correspondent granted',
            self::CorrespondentRevoked => 'Correspondent revoked',
            self::ArticleUnpublished => 'Article unpublished',
            self::CritiqueGroupDissolved => 'Critique group dissolved',
            self::CritiqueGroupMemberRemoved => 'Critique group member removed',
            self::CritiqueGroupMemberMoved => 'Critique group member moved',
            self::SettingsUpdated => 'Settings updated',
            self::TagMerged => 'Tag merged',
            self::TagDeleted => 'Tag deleted',
            self::ContactMessageDeleted => 'Contact message deleted',
        };
    }
}
