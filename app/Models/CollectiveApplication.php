<?php

namespace App\Models;

use App\Enums\CollectiveApplicationStatus;
use Database\Factories\CollectiveApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $collective_id
 * @property int $user_id
 * @property string $message
 * @property CollectiveApplicationStatus $status
 * @property int|null $decided_by
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['collective_id', 'user_id', 'message', 'status', 'decided_by', 'decided_at'])]
class CollectiveApplication extends Model
{
    /** @use HasFactory<CollectiveApplicationFactory> */
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
            'collective_id' => 'integer',
            'user_id' => 'integer',
            'message' => 'string',
            'status' => CollectiveApplicationStatus::class,
            'decided_by' => 'integer',
            'decided_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Collective, $this>
     */
    public function collective(): BelongsTo
    {
        return $this->belongsTo(Collective::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The founder who accepted or declined the application.
     *
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
