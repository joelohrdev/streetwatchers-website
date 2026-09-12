<?php

namespace App\Models;

use App\Enums\ChapterStatus;
use Carbon\Carbon;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 */
#[Fillable('name', 'slug', 'city', 'country', 'latitude', 'longitude', 'description', 'cover_image_path', 'status')]
class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

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
}
