<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        $role = fake()->randomElement(['admin', 'teacher', 'teacher_support', 'student']);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'gender' => $gender,
            'role' => $role,
            'class_id' => null,
            'phone' => fake()->phoneNumber(),
            'guardian_name' => $role === 'student' ? fake()->name() : null,
            'guardian_phone' => $role === 'student' ? fake()->phoneNumber() : null,
            'locale' => 'ar',
            'password' => static::$password ??= Hash::make('password'),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'gender' => fake()->randomElement(['male', 'female']),
            'status' => 'active',
        ]);
    }

    /**
     * Create a teacher user.
     */
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher',
            'gender' => fake()->randomElement(['male', 'female']),
            'status' => 'active',
        ]);
    }

    /**
     * Create a teacher support user.
     */
    public function teacherSupport(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher_support',
            'gender' => fake()->randomElement(['male', 'female']),
            'status' => 'active',
        ]);
    }

    /**
     * Create a student user.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
            'gender' => fake()->randomElement(['male', 'female']),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
            'status' => 'active',
        ]);
    }

    /**
     * Create a male user.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'male',
        ]);
    }

    /**
     * Create a female user.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'female',
        ]);
    }
}
