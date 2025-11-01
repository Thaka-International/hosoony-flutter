<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(['web', 'android', 'ios']);

        return [
            'user_id' => \App\Models\User::factory(),
            'fcm_token' => fake()->optional(0.8)->sha256(),
            'platform' => $platform,
            'last_seen_at' => fake()->optional(0.9)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Create a web device.
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'web',
            'fcm_token' => null,
        ]);
    }

    /**
     * Create an Android device.
     */
    public function android(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'android',
            'fcm_token' => fake()->sha256(),
        ]);
    }

    /**
     * Create an iOS device.
     */
    public function ios(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'ios',
            'fcm_token' => fake()->sha256(),
        ]);
    }

    /**
     * Create an active device (seen recently).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an inactive device (not seen recently).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => fake()->dateTimeBetween('-60 days', '-31 days'),
        ]);
    }
}
