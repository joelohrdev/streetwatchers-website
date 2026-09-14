<?php

namespace Database\Factories;

use App\Models\CritiqueComment;
use App\Models\CritiqueSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CritiqueComment>
 */
class CritiqueCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'critique_submission_id' => CritiqueSubmission::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
