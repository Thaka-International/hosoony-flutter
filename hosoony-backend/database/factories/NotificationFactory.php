<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['info', 'success', 'warning', 'error', 'reminder']),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'data' => $this->faker->optional()->randomElements(['amount' => 100, 'currency' => 'SAR']),
            'sent_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'read_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['pending', 'sent', 'failed', 'read']),
            'channel' => $this->faker->randomElement(['in_app', 'email', 'sms', 'push']),
        ];
    }

    /**
     * Indicate that the notification is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is a payment reminder.
     */
    public function paymentReminder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'reminder',
            'title' => 'تذكير بالدفع',
            'message' => 'يرجى دفع رسوم الاشتراك',
            'data' => ['amount' => 100, 'currency' => 'SAR'],
        ]);
    }

    /**
     * Indicate that the notification is a subscription expiry.
     */
    public function subscriptionExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'reminder',
            'title' => 'انتهاء الاشتراك',
            'message' => 'اشتراكك سينتهي قريباً',
            'data' => ['expiry_date' => now()->addDays(7)->format('Y-m-d')],
        ]);
    }
}