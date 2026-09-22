<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;

class ChapterPolicy
{
    /**
     * Organizers plan the group's meetups and choose its other organizers.
     */
    public function organize(User $user, Chapter $chapter): bool
    {
        return $chapter->isOrganizedBy($user);
    }
}
