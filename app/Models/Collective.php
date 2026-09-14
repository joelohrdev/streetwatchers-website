<?php

namespace App\Models;

use Database\Factories\CollectiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string|null $website_url
 * @property string|null $instagram_url
 * @property string|null $logo_path
 * @property bool $is_open_for_applications
 * @property bool $is_verified
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'slug', 'description', 'website_url', 'instagram_url', 'logo_path', 'is_open_for_applications', 'is_verified'])]
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
     * @return HasMany<CollectiveApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CollectiveApplication::class);
    }
}
