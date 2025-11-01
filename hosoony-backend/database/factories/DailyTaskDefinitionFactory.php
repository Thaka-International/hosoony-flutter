<?php

namespace Database\Factories;

use App\Models\DailyTaskDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyTaskDefinition>
 */
class DailyTaskDefinitionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DailyTaskDefinition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['hifz', 'murajaah', 'tilawah', 'tajweed', 'tafseer']),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['hifz', 'murajaah', 'tilawah', 'tajweed', 'tafseer']),
            'points_weight' => fake()->randomElement([10, 15, 20]),
            'duration_minutes' => fake()->numberBetween(15, 60),
            'is_active' => true,
        ];
    }

    /**
     * Create a hifz task definition.
     */
    public function hifz(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'hifz',
            'description' => 'حفظ القرآن الكريم',
            'type' => 'hifz',
            'points_weight' => 20,
            'duration_minutes' => 30,
        ]);
    }

    /**
     * Create a murajaah task definition.
     */
    public function murajaah(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'murajaah',
            'description' => 'مراجعة القرآن الكريم',
            'type' => 'murajaah',
            'points_weight' => 20,
            'duration_minutes' => 25,
        ]);
    }

    /**
     * Create a tilawah task definition.
     */
    public function tilawah(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'tilawah',
            'description' => 'تلاوة القرآن الكريم',
            'type' => 'tilawah',
            'points_weight' => 15,
            'duration_minutes' => 20,
        ]);
    }

    /**
     * Create a tajweed task definition.
     */
    public function tajweed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'tajweed',
            'description' => 'تعلم أحكام التجويد',
            'type' => 'tajweed',
            'points_weight' => 15,
            'duration_minutes' => 25,
        ]);
    }

    /**
     * Create a tafseer task definition.
     */
    public function tafseer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'tafseer',
            'description' => 'تعلم تفسير القرآن الكريم',
            'type' => 'tafseer',
            'points_weight' => 10,
            'duration_minutes' => 30,
        ]);
    }
}