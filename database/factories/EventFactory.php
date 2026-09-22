<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::instance(fake()->dateTimeBetween('-1 month', '+2 months'))->setMinute(0)->setSecond(0);

        return [
            'chapter_id' => Chapter::factory(),
            'organizer_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'location_name' => fake()->streetAddress(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(fake()->numberBetween(1, 4)),
            'timezone' => 'UTC',
        ];
    }

    /**
     * A meetup a week from now, two hours long.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
        ]);
    }

    /**
     * Indicate that the meetup takes RSVPs, optionally with a limit on places.
     */
    public function takingRsvps(?int $limit = null): static
    {
        return $this->state(fn (array $attributes) => [
            'rsvps_enabled' => true,
            'rsvp_limit' => $limit,
        ]);
    }

    /**
     * Indicate that the meetup has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => now(),
        ]);
    }
}
