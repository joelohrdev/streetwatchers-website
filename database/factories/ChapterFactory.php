<?php

namespace Database\Factories;

use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
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
            'slug' => fake()->slug(),
            'city' => fake()->city(),
            'country' => fake()->randomElement(Country::cases()),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'description' => fake()->text(),
            'status' => fake()->randomElement(ChapterStatus::cases()),
        ];
    }

    /**
     * Indicate that the chapter is awaiting approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChapterStatus::Pending,
        ]);
    }

    /**
     * Indicate that the chapter is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChapterStatus::Active,
        ]);
    }
}
