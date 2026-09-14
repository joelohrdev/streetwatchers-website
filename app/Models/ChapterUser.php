<?php

namespace App\Models;

use App\Enums\ChapterMemberRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $chapter_id
 * @property int $user_id
 * @property ChapterMemberRole $role
 * @property Carbon $joined_at
 */
class ChapterUser extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'chapter_id' => 'integer',
            'user_id' => 'integer',
            'role' => ChapterMemberRole::class,
            'joined_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
