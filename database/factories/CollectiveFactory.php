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
            'website_url' => fake()->optional()->url(),
            'instagram_url' => fake()->optional()->passthrough('https://instagram.com/'.fake()->userName()),
            'logo_path' => null,
            'is_open_for_applications' => fake()->boolean(),
        ];
    }
}
