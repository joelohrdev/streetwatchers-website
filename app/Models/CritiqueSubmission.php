<?php

namespace App\Models;

use Database\Factories\CritiqueSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $critique_group_id
 * @property int $photo_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['critique_group_id', 'photo_id', 'user_id'])]
class CritiqueSubmission extends Model
{
    /** @use HasFactory<CritiqueSubmissionFactory> */
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
            'critique_group_id' => 'integer',
            'photo_id' => 'integer',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CritiqueGroup, $this>
     */
    public function critiqueGroup(): BelongsTo
    {
        return $this->belongsTo(CritiqueGroup::class);
    }

    /**
     * @return BelongsTo<Photo, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CritiqueComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(CritiqueComment::class);
    }
}
