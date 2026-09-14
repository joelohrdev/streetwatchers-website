<?php

namespace Database\Factories;

use App\Models\Correspondent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Correspondent>
 */
class CorrespondentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(),
            'is_active' => fake()->boolean(),
        ];
    }
}
