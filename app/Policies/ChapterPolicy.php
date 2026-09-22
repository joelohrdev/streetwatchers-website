<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;

class ChapterPolicy
{
    /**
     * Organisers plan the group's meetups and choose its other organisers.
     */
    public function organise(User $user, Chapter $chapter): bool
    {
        return $chapter->isOrganisedBy($user);
    }
}
