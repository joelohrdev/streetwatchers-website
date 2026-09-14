<?php

namespace App\Models;

use App\Enums\AuditAction;
use BackedEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * An append-only record of an administrative or moderation action.
 *
 * @property int $id
 * @property int|null $actor_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property AuditAction $action
 * @property string|null $old_status
 * @property string|null $new_status
 * @property string|null $reason
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 */
#[Fillable(['actor_id', 'subject_type', 'subject_id', 'action', 'old_status', 'new_status', 'reason', 'metadata'])]
class AuditLog extends Model
{
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'actor_id' => 'integer',
            'subject_type' => 'string',
            'subject_id' => 'integer',
            'action' => AuditAction::class,
            'old_status' => 'string',
            'new_status' => 'string',
            'reason' => 'string',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Record an action taken by a user against an optional subject.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function record(
        ?User $actor,
        AuditAction $action,
        ?Model $subject = null,
        BackedEnum|string|null $oldStatus = null,
        BackedEnum|string|null $newStatus = null,
        ?string $reason = null,
        ?array $metadata = null,
    ): self {
        return self::create([
            'actor_id' => $actor?->id,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'old_status' => $oldStatus instanceof BackedEnum ? $oldStatus->value : $oldStatus,
            'new_status' => $newStatus instanceof BackedEnum ? $newStatus->value : $newStatus,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Shape the entry for an admin activity timeline. Expects the actor relation to be loaded.
     *
     * @return array{id: int, action: string, old_status: string|null, new_status: string|null, reason: string|null, actor: string|null, created_at: string}
     */
    public function toTimelineEntry(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action->label(),
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'reason' => $this->reason,
            'actor' => $this->actor?->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
