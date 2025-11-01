<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => \App\Models\ClassModel::factory(),
            'teacher_id' => \App\Models\User::factory()->teacher(),
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'starts_at' => fake()->dateTimeBetween('now', '+1 month'),
            'ends_at' => fake()->dateTimeBetween('+1 hour', '+2 hours'),
            'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'scheduled']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'completed']);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => fake()->dateTimeBetween('+1 day', '+1 week'),
            'status' => 'scheduled',
        ]);
    }
}
