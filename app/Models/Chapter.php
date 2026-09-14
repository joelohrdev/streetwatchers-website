<?php

namespace App\Models;

use App\Enums\ChapterStatus;
use Carbon\Carbon;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $city
 * @property string $country
 * @property float $latitude
 * @property float $longitude
 * @property string $description
 * @property string|null $cover_image_path
 * @property ChapterStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $admins_count
 */
#[Fillable('name', 'slug', 'city', 'country', 'latitude', 'longitude', 'description', 'cover_image_path', 'status')]
class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
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
            'slug' => 'string',
            'city' => 'string',
            'country' => 'string',
            'latitude' => 'float',
            'longitude' => 'float',
            'description' => 'string',
            'cover_image_path' => 'string',
            'status' => ChapterStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this, ChapterUser>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(ChapterUser::class)
            ->withPivot('role', 'joined_at');
    }

    /**
     * The membership rows for this chapter, for when the role and join date matter more than the user.
     *
     * @return HasMany<ChapterUser, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(ChapterUser::class);
    }

    /**
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
