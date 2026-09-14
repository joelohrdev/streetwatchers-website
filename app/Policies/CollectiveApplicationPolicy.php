<?php

namespace App\Policies;

use App\Models\CollectiveApplication;
use App\Models\User;

class CollectiveApplicationPolicy
{
    /**
     * Only founders of the collective applied to can accept or decline an application.
     */
    public function decide(User $user, CollectiveApplication $collectiveApplication): bool
    {
        return $collectiveApplication->collective->isFoundedBy($user);
    }
}
