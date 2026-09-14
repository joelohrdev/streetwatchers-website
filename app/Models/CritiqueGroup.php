<?php

namespace App\Models;

use App\Enums\CritiqueGroupStatus;
use Database\Factories\CritiqueGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $name
 * @property Carbon $week_start
 * @property Carbon $week_end
 * @property int $max_members
 * @property CritiqueGroupStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'week_start', 'week_end', 'max_members', 'status'])]
class CritiqueGroup extends Model
{
    /** @use HasFactory<CritiqueGroupFactory> */
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
            'name' => 'string',
            'week_start' => 'date',
            'week_end' => 'date',
            'max_members' => 'integer',
            'status' => CritiqueGroupStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<User, $this, CritiqueGroupUser>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(CritiqueGroupUser::class)
            ->withPivot('joined_at');
    }

    /**
     * @return HasMany<CritiqueSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(CritiqueSubmission::class);
    }
}
