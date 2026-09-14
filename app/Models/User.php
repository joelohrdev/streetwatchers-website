<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\CollectiveMemberRole;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $bio
 * @property string|null $avatar_path
 * @property string|null $city
 * @property string|null $country
 * @property UserRole $role
 * @property UserStatus $status
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'bio', 'avatar_path', 'city', 'country', 'role', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'email' => 'string',
            'password' => 'hashed',
            'bio' => 'string',
            'avatar_path' => 'string',
            'city' => 'string',
            'country' => 'string',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Whether the user can manage the whole platform from the admin panel.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Whether the user is allowed to sign in and use the platform.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * @return BelongsToMany<Chapter, $this, ChapterUser>
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class)
            ->using(ChapterUser::class)
            ->withPivot('role', 'joined_at');
    }

    /**
     * The user's group membership rows, for when the role matters as well as the group.
     *
     * @return HasMany<ChapterUser, $this>
     */
    public function chapterMemberships(): HasMany
    {
        return $this->hasMany(ChapterUser::class);
    }

    /**
     * The user's collective membership rows, for when the role matters as well as the collective.
     *
     * @return HasMany<CollectiveUser, $this>
     */
    public function collectiveMemberships(): HasMany
    {
        return $this->hasMany(CollectiveUser::class);
    }

    /**
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
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
     * @return BelongsToMany<Photo, $this>
     */
    public function likedPhotos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'likes')->withTimestamps();
    }

    /**
     * The users who follow this user.
     *
     * @return BelongsToMany<User, $this, Follow>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->using(Follow::class)
            ->withTimestamps(updatedAt: false);
    }

    /**
     * The users this user follows.
     *
     * @return BelongsToMany<User, $this, Follow>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->using(Follow::class)
            ->withTimestamps(updatedAt: false);
    }

    /**
     * @return BelongsToMany<Collective, $this, CollectiveUser>
     */
    public function collectives(): BelongsToMany
    {
        return $this->belongsToMany(Collective::class)
            ->using(CollectiveUser::class)
            ->withPivot('role');
    }

    /**
     * The collectives this user founded.
     *
     * @return BelongsToMany<Collective, $this, CollectiveUser>
     */
    public function foundedCollectives(): BelongsToMany
    {
        return $this->collectives()->wherePivot('role', CollectiveMemberRole::Founder);
    }

    /**
     * @return HasMany<CollectiveApplication, $this>
     */
    public function collectiveApplications(): HasMany
    {
        return $this->hasMany(CollectiveApplication::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * The events this user has responded to, with the RSVP status on the pivot.
     *
     * @return BelongsToMany<Event, $this, EventRsvp>
     */
    public function rsvpedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_rsvps')
            ->using(EventRsvp::class)
            ->withPivot('status');
    }

    /**
     * @return HasOne<Correspondent, $this>
     */
    public function correspondent(): HasOne
    {
        return $this->hasOne(Correspondent::class);
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * The reports this user has filed.
     *
     * @return HasMany<Report, $this>
     */
    public function filedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * The reports filed against this user.
     *
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
