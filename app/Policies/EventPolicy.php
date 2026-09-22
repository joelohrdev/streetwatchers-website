<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Any organiser of the meetup's group can edit or cancel it, not only the one who created it.
     */
    public function update(User $user, Event $event): bool
    {
        return $event->chapter->isOrganisedBy($user);
    }
}
