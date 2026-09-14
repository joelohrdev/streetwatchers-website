<?php

namespace App\Models;

use App\Enums\CollectiveMemberRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Collective, $this>
     */
    public function collective(): BelongsTo
    {
        return $this->belongsTo(Collective::class);
    }
}
