<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionItem>
 */
class SessionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => \App\Models\Session::factory(),
            'title' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['hifz', 'murajaah', 'tilawah', 'tajweed', 'tafseer', 'other']),
            'duration_minutes' => fake()->numberBetween(15, 120),
            'order' => fake()->numberBetween(1, 10),
            'content' => fake()->optional()->paragraph(),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'skipped']),
        ];
    }

    public function hifz(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'hifz']);
    }

    public function murajaah(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'murajaah']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'completed']);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }
}
