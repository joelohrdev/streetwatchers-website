<?php

namespace App\Models;

use App\Enums\ChapterStatus;
use App\Enums\Country;
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
 * @property Country $country
 * @property float $latitude
 * @property float $longitude
 * @property string $description
 * @property string|null $cover_image_path
 * @property ChapterStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $admins_count
 * @property-read int|null $published_photos_count
 */
#[Fillable('name', 'slug', 'city', 'country', 'latitude', 'longitude', 'description', 'cover_image_path', 'status')]
class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    private const KILOMETRES_PER_MILE = 1.609344;

    /**
     * Slightly under the true figure (about 69), so the latitude pre-filter never drops a chapter that's in range.
     */
    private const MILES_PER_DEGREE_OF_LATITUDE = 68;

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
            'country' => Country::class,
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
     * Great-circle distance to another chapter in kilometres, using the haversine formula.
     */
    public function distanceInKilometresTo(Chapter $other): float
    {
        $latitudeDelta = deg2rad($other->latitude - $this->latitude);
        $longitudeDelta = deg2rad($other->longitude - $this->longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * 6371 * asin(sqrt($a));
    }

    /**
     * Great-circle distance to another chapter in miles.
     */
    public function distanceInMilesTo(Chapter $other): float
    {
        return $this->distanceInKilometresTo($other) / self::KILOMETRES_PER_MILE;
    }

    /**
     * The closest active or pending chapter within the given number of miles of a point, if there is one.
     * Inactive chapters don't count, so a lapsed city can be started again.
     */
    public static function nearestWithinMiles(float $latitude, float $longitude, float $miles): ?self
    {
        $point = new self(['latitude' => $latitude, 'longitude' => $longitude]);
        $latitudeSpan = $miles / self::MILES_PER_DEGREE_OF_LATITUDE;

        return self::query()
            ->whereIn('status', [ChapterStatus::Active, ChapterStatus::Pending])
            ->whereBetween('latitude', [$latitude - $latitudeSpan, $latitude + $latitudeSpan])
            ->get()
            ->filter(fn (Chapter $chapter): bool => $point->distanceInMilesTo($chapter) < $miles)
            ->sortBy(fn (Chapter $chapter): float => $point->distanceInMilesTo($chapter))
            ->first();
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
