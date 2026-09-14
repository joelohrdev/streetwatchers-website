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

    /**
     * Indicate that the application is waiting for a founder's decision.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CollectiveApplicationStatus::Pending,
            'decided_by' => null,
            'decided_at' => null,
        ]);
    }

    /**
     * Indicate that a founder declined the application.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CollectiveApplicationStatus::Declined,
            'decided_at' => now()->subDay(),
        ]);
    }
}
