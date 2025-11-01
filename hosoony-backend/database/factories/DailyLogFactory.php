<?php

namespace Database\Factories;

use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyLog>
 */
class DailyLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DailyLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'log_date' => fake()->date(),
            'status' => fake()->randomElement(['pending', 'submitted', 'verified', 'rejected']),
            'finish_order' => fake()->numberBetween(1, 10),
            'verified_by' => null,
            'verified_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Create a submitted log.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'finish_order' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Create a verified log.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_by' => User::factory()->teacher(),
            'verified_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a rejected log.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'verified_by' => User::factory()->teacher(),
            'verified_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'notes' => fake()->sentence(),
        ]);
    }

    /**
     * Create a log for specific date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'log_date' => $date,
        ]);
    }

    /**
     * Create a log with specific finish order.
     */
    public function withFinishOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'finish_order' => $order,
        ]);
    }
}