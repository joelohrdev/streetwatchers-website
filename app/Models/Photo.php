<?php

namespace App\Models;

use App\Enums\PhotoConsentType;
use App\Enums\PhotoStatus;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $chapter_id
 * @property string|null $title
 * @property string|null $caption
 * @property string $context_story
 * @property string $image_path
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $location_name
 * @property Carbon|null $taken_at
 * @property PhotoConsentType $consent_type
 * @property PhotoStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'chapter_id', 'title', 'caption', 'context_story', 'image_path', 'latitude', 'longitude', 'location_name', 'taken_at', 'consent_type', 'status'])]
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (Photo $photo): void {
            $photo->status ??= Setting::newPhotosRequireReview() ? PhotoStatus::Pending : PhotoStatus::Published;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'chapter_id' => 'integer',
            'title' => 'string',
            'caption' => 'string',
            'context_story' => 'string',
            'image_path' => 'string',
            'latitude' => 'float',
            'longitude' => 'float',
            'location_name' => 'string',
            'taken_at' => 'date',
            'consent_type' => PhotoConsentType::class,
            'status' => PhotoStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Chapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    /**
     * @return HasMany<CritiqueSubmission, $this>
     */
    public function critiqueSubmissions(): HasMany
    {
        return $this->hasMany(CritiqueSubmission::class);
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
