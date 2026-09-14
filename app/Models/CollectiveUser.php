<?php

namespace App\Models;

use App\Enums\CollectiveMemberRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $collective_id
 * @property int $user_id
 * @property CollectiveMemberRole $role
 */
class CollectiveUser extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collective_id' => 'integer',
            'user_id' => 'integer',
            'role' => CollectiveMemberRole::class,
        ];
    }
}
