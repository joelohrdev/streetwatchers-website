<?php

namespace Database\Factories;

use App\Enums\PhotoConsentType;
use App\Enums\PhotoStatus;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
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
            'chapter_id' => null,
            'title' => fake()->optional()->sentence(3),
            'caption' => fake()->optional()->sentence(),
            'context_story' => fake()->paragraph(),
            'image_path' => 'photos/'.fake()->uuid().'.jpg',
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'location_name' => fake()->streetName(),
            'taken_at' => fake()->optional()->date(),
            'consent_type' => fake()->randomElement(PhotoConsentType::cases()),
            'status' => fake()->randomElement(PhotoStatus::cases()),
        ];
    }

    /**
     * Indicate that the photo is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhotoStatus::Published,
        ]);
    }
}
