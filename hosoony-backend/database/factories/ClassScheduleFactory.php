<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSchedule>
 */
class ClassScheduleFactory extends Factory
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
            'day_of_week' => fake()->randomElement(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
            'room' => fake()->optional()->word(),
            'notes' => fake()->optional()->sentence(),
            'is_active' => fake()->boolean(80),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
