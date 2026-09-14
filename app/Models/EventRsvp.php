<?php

namespace App\Models;

use App\Enums\EventRsvpStatus;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $event_id
 * @property int $user_id
 * @property EventRsvpStatus $status
 */
#[Table('event_rsvps')]
class EventRsvp extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'user_id' => 'integer',
            'status' => EventRsvpStatus::class,
        ];
    }
}
