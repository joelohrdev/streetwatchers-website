<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $follower_id
 * @property int $following_id
 * @property Carbon $created_at
 */
#[Table('follows')]
class Follow extends Pivot
{
    /**
     * The follows table only records when the follow was created.
     *
     * Pivots inherit timestamp column names from the parent model, so the
     * UPDATED_AT constant alone is not enough to skip the column.
     */
    public function getUpdatedAtColumn(): ?string
    {
        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'follower_id' => 'integer',
            'following_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
