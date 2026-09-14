<?php

namespace Database\Factories;

use App\Enums\CollectiveApplicationStatus;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectiveApplication>
 */
class CollectiveApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collective_id' => Collective::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'status' => fake()->randomElement(CollectiveApplicationStatus::cases()),
        ];
    }
}
