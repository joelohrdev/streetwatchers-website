<?php

namespace Database\Factories;

use App\Models\CritiqueGroup;
use App\Models\CritiqueSubmission;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CritiqueSubmission>
 */
class CritiqueSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'critique_group_id' => CritiqueGroup::factory(),
            'photo_id' => Photo::factory(),
            'user_id' => fn (array $attributes) => Photo::whereKey($attributes['photo_id'])->value('user_id'),
        ];
    }
}
