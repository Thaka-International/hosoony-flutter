<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'subscription_id' => Subscription::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'currency' => 'SAR',
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'online']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'transaction_id' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'paid_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the payment is cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    /**
     * Indicate that the payment is bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
        ]);
    }
}