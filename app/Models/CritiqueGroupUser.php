<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $critique_group_id
 * @property int $user_id
 * @property Carbon $joined_at
 */
class CritiqueGroupUser extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'critique_group_id' => 'integer',
            'user_id' => 'integer',
            'joined_at' => 'datetime',
        ];
    }
}
