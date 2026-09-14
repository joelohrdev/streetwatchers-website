<?php

namespace App\Policies;

use App\Models\Collective;
use App\Models\User;

class CollectivePolicy
{
    /**
     * Founders edit their collective's details and review its applications.
     */
    public function update(User $user, Collective $collective): bool
    {
        return $collective->isFoundedBy($user);
    }
}
