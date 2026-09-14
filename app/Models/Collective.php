<?php

namespace App\Models;

use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use Database\Factories\CollectiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string|null $based_in
 * @property string|null $website_url
 * @property string|null $instagram_url
 * @property string|null $logo_path
 * @property bool $is_open_for_applications
 * @property bool $is_verified
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'slug', 'description', 'based_in', 'website_url', 'instagram_url', 'logo_path', 'is_open_for_applications', 'is_verified'])]
class Collective extends Model
{
    /** @use HasFactory<CollectiveFactory> */
    use HasFactory, SoftDeletes;

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
            'description' => 'string',
            'based_in' => 'string',
            'website_url' => 'string',
            'instagram_url' => 'string',
            'logo_path' => 'string',
            'is_open_for_applications' => 'boolean',
            'is_verified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
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
     * @return BelongsToMany<User, $this, CollectiveUser>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(CollectiveUser::class)
            ->withPivot('role');
    }

    /**
     * The membership rows for this collective, for when the role matters more than the user.
     *
     * @return HasMany<CollectiveUser, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(CollectiveUser::class);
    }

    /**
     * @return HasMany<CollectiveApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CollectiveApplication::class);
    }

    /**
     * The user's role in the collective, or null when they are not in it.
     */
    public function roleOf(User $user): ?CollectiveMemberRole
    {
        return $this->memberships()->where('user_id', $user->id)->first()?->role;
    }

    public function isFoundedBy(User $user): bool
    {
        return $this->roleOf($user) === CollectiveMemberRole::Founder;
    }

    public function hasPendingApplicationFrom(User $user): bool
    {
        return $this->applications()
            ->where('user_id', $user->id)
            ->where('status', CollectiveApplicationStatus::Pending)
            ->exists();
    }

    /**
     * The public URL of the collective's logo, if one was uploaded.
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
