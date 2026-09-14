<?php

namespace Database\Factories;

use App\Models\Collective;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Collective>
 */
class CollectiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'based_in' => fake()->optional()->city(),
            'website_url' => fake()->optional()->url(),
            'instagram_url' => fake()->optional()->passthrough('https://instagram.com/'.fake()->userName()),
            'logo_path' => null,
            'is_open_for_applications' => fake()->boolean(),
            'is_verified' => false,
        ];
    }

    /**
     * Indicate that the collective is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the collective is accepting applications.
     */
    public function openForApplications(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open_for_applications' => true,
        ]);
    }

    /**
     * Indicate that the collective is not accepting applications.
     */
    public function closedForApplications(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open_for_applications' => false,
        ]);
    }
}
