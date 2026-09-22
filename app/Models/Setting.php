<?php

namespace App\Models;

use App\Enums\SettingKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'value'])]
#[Table(key: 'key', keyType: 'string', incrementing: false)]
class Setting extends Model
{
    const CREATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => 'string',
            'value' => 'string',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the stored value for a setting, or null when it has never been set.
     */
    public static function valueOf(SettingKey $key): ?string
    {
        // Wrapped in an array so an unset (null) value is cached too, instead of querying on every request.
        return Cache::rememberForever(
            self::cacheKey($key),
            fn (): array => ['value' => self::query()->whereKey($key->value)->value('value')],
        )['value'];
    }

    /**
     * Store the value for a setting and refresh its cached copy.
     */
    public static function store(SettingKey $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key->value], ['value' => $value]);

        Cache::forget(self::cacheKey($key));
    }

    /**
     * Whether newly created photos wait for review before being published.
     */
    public static function newPhotosRequireReview(): bool
    {
        return (self::valueOf(SettingKey::NewPhotosRequireReview) ?? '1') === '1';
    }

    /**
     * The site-wide announcement, or null when no banner should be shown.
     */
    public static function announcementBanner(): ?string
    {
        $banner = trim((string) self::valueOf(SettingKey::AnnouncementBanner));

        return $banner === '' ? null : $banner;
    }

    /**
     * How far apart groups must be, in miles. A new group can't be proposed closer than this to an active or pending one.
     */
    public static function groupRadiusInMiles(): int
    {
        return (int) (self::valueOf(SettingKey::GroupRadiusMiles) ?? 25);
    }

    private static function cacheKey(SettingKey $key): string
    {
        return 'settings.'.$key->value;
    }
}
