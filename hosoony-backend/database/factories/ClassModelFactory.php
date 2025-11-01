<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassModel>
 */
class ClassModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => \App\Models\Program::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'gender' => fake()->randomElement(['male', 'female']),
            'max_students' => fake()->numberBetween(10, 30),
            'current_students' => fake()->numberBetween(0, 20),
            'status' => fake()->randomElement(['active', 'inactive', 'completed']),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => ['gender' => 'male']);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => ['gender' => 'female']);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'active']);
    }
}
