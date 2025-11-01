<?php

namespace Database\Factories;

use App\Models\QuranSegment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuranSegment>
 */
class QuranSegmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QuranSegment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['سورة الفاتحة', 'سورة البقرة', 'سورة آل عمران', 'سورة النساء', 'سورة المائدة']),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['surah', 'juz', 'page', 'ayah']),
            'start_ayah' => fake()->numberBetween(1, 100),
            'end_ayah' => fake()->numberBetween(101, 200),
            'start_page' => fake()->numberBetween(1, 50),
            'end_page' => fake()->numberBetween(51, 100),
            'order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    /**
     * Create a surah segment.
     */
    public function surah(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['سورة الفاتحة', 'سورة البقرة', 'سورة آل عمران']),
            'type' => 'surah',
            'start_ayah' => fake()->numberBetween(1, 50),
            'end_ayah' => fake()->numberBetween(51, 100),
        ]);
    }

    /**
     * Create a juz segment.
     */
    public function juz(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['الجزء الأول', 'الجزء الثاني', 'الجزء الثالث']),
            'type' => 'juz',
            'start_page' => fake()->numberBetween(1, 20),
            'end_page' => fake()->numberBetween(21, 40),
        ]);
    }

    /**
     * Create a page segment.
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['الصفحة الأولى', 'الصفحة الثانية', 'الصفحة الثالثة']),
            'type' => 'page',
            'start_page' => fake()->numberBetween(1, 10),
            'end_page' => fake()->numberBetween(11, 20),
        ]);
    }

    /**
     * Create an inactive segment.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}