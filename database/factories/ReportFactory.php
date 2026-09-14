<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Photo;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reportable_type' => (new Photo)->getMorphClass(),
            'reportable_id' => Photo::factory(),
            'reporter_id' => User::factory(),
            'reporter_email' => null,
            'is_public_submission' => false,
            'reason' => fake()->paragraph(),
            'status' => fake()->randomElement(ReportStatus::cases()),
        ];
    }

    /**
     * Indicate that the report was submitted without logging in.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => null,
        ]);
    }

    /**
     * Indicate that the report came from the public removal request form.
     */
    public function publicSubmission(): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => null,
            'reporter_email' => fake()->safeEmail(),
            'is_public_submission' => true,
        ]);
    }

    /**
     * Indicate that the report is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Open,
        ]);
    }
}
