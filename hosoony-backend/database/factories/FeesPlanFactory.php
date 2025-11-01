<?php

namespace Database\Factories;

use App\Models\FeesPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeesPlan>
 */
class FeesPlanFactory extends Factory
{
    protected $model = FeesPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Plan',
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'currency' => 'SAR',
            'billing_period' => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
            'duration_months' => $this->faker->randomElement([1, 3, 6, 12]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the fees plan is monthly.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Monthly Plan',
            'billing_period' => 'monthly',
            'duration_months' => 1,
            'amount' => 100,
        ]);
    }

    /**
     * Indicate that the fees plan is quarterly.
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Quarterly Plan',
            'billing_period' => 'quarterly',
            'duration_months' => 3,
            'amount' => 250,
        ]);
    }

    /**
     * Indicate that the fees plan is yearly.
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Yearly Plan',
            'billing_period' => 'yearly',
            'duration_months' => 12,
            'amount' => 1000,
        ]);
    }

    /**
     * Indicate that the fees plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}


