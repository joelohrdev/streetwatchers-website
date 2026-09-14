<?php

namespace Database\Factories;

use App\Enums\ContactTopic;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'topic' => fake()->randomElement(ContactTopic::cases()),
            'message' => fake()->paragraphs(2, true),
            'read_at' => null,
        ];
    }

    /**
     * Indicate that a super admin has already read the message.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now()->subHour(),
        ]);
    }
}
