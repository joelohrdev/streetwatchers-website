<?php

namespace Database\Factories;

use App\Enums\CritiqueGroupStatus;
use App\Models\CritiqueGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CritiqueGroup>
 */
class CritiqueGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weekStart = Carbon::instance(fake()->dateTimeBetween('-4 weeks', '+2 weeks'))->startOfWeek();

        return [
            'name' => fake()->optional()->words(2, true),
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->endOfWeek(),
            'max_members' => 8,
            'status' => fake()->randomElement(CritiqueGroupStatus::cases()),
        ];
    }
}
