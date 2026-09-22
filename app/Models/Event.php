<?php

namespace App\Models;

use App\Enums\EventRsvpStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $chapter_id
 * @property int $organizer_id
 * @property string $title
 * @property string $description
 * @property string $location_name
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string $timezone
 * @property bool $rsvps_enabled
 * @property int|null $rsvp_limit
 * @property Carbon|null $canceled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['chapter_id', 'organizer_id', 'title', 'description', 'location_name', 'latitude', 'longitude', 'starts_at', 'ends_at', 'timezone', 'rsvps_enabled', 'rsvp_limit', 'canceled_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'chapter_id' => 'integer',
            'organizer_id' => 'integer',
            'title' => 'string',
            'description' => 'string',
            'location_name' => 'string',
            'latitude' => 'float',
            'longitude' => 'float',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'timezone' => 'string',
            'rsvps_enabled' => 'boolean',
            'rsvp_limit' => 'integer',
            'canceled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Chapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * The users who have responded to this event, with the RSVP status on the pivot.
     *
     * @return BelongsToMany<User, $this, EventRsvp>
     */
    public function rsvps(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_rsvps')
            ->using(EventRsvp::class)
            ->withPivot('status');
    }

    /**
     * The users who are going, the only RSVP status meetups use.
     *
     * @return BelongsToMany<User, $this, EventRsvp>
     */
    public function attendees(): BelongsToMany
    {
        return $this->rsvps()->wherePivot('status', EventRsvpStatus::Going);
    }

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * Whether someone new can RSVP: RSVPs are on, the meetup is still ahead and there is room.
     */
    public function isAcceptingRsvps(): bool
    {
        return $this->rsvps_enabled
            && ! $this->isCanceled()
            && ! $this->hasEnded()
            && ($this->rsvp_limit === null || $this->attendees()->count() < $this->rsvp_limit);
    }
}
