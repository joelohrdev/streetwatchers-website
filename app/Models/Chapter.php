<?php

namespace App\Models;

use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'slug', 'city', 'country', 'latitude', 'longitude', 'description', 'cover_image_path', 'status')]
class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;
}
