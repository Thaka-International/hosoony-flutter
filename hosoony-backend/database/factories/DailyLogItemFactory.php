<?php

namespace Database\Factories;

use App\Models\DailyLogItem;
use App\Models\DailyLog;
use App\Models\DailyTaskDefinition;
use App\Models\QuranSegment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyLogItem>
 */
class DailyLogItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DailyLogItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_log_id' => DailyLog::factory(),
            'task_definition_id' => DailyTaskDefinition::factory(),
            'quran_segment_id' => QuranSegment::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'duration_minutes' => fake()->numberBetween(15, 60),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'skipped']),
            'proof_type' => fake()->randomElement(['none', 'note', 'audio', 'video']),
        ];
    }

    /**
     * Create a completed item.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'proof_type' => fake()->randomElement(['note', 'audio', 'video']),
            'notes' => fake()->sentence(),
        ]);
    }

    /**
     * Create a pending item.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'proof_type' => 'none',
            'notes' => null,
        ]);
    }

    /**
     * Create a hifz item.
     */
    public function hifz(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_definition_id' => DailyTaskDefinition::factory()->hifz(),
            'quantity' => fake()->numberBetween(1, 3),
            'duration_minutes' => fake()->numberBetween(20, 40),
        ]);
    }

    /**
     * Create a murajaah item.
     */
    public function murajaah(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_definition_id' => DailyTaskDefinition::factory()->murajaah(),
            'quantity' => fake()->numberBetween(2, 5),
            'duration_minutes' => fake()->numberBetween(15, 30),
        ]);
    }

    /**
     * Create a tilawah item.
     */
    public function tilawah(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_definition_id' => DailyTaskDefinition::factory()->tilawah(),
            'quantity' => fake()->numberBetween(1, 2),
            'duration_minutes' => fake()->numberBetween(10, 25),
        ]);
    }

    /**
     * Create a tajweed item.
     */
    public function tajweed(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_definition_id' => DailyTaskDefinition::factory()->tajweed(),
            'quantity' => 1,
            'duration_minutes' => fake()->numberBetween(20, 30),
        ]);
    }

    /**
     * Create a tafseer item.
     */
    public function tafseer(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_definition_id' => DailyTaskDefinition::factory()->tafseer(),
            'quantity' => 1,
            'duration_minutes' => fake()->numberBetween(25, 40),
        ]);
    }
}