<?php

namespace Tests\Feature;

use App\Models\FeesPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $teacherUser;
    protected User $studentUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('teacher');
        Role::findOrCreate('student');

        $this->adminUser = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $this->teacherUser = User::factory()->teacher()->create(['email' => 'teacher@example.com']);
        $this->studentUser = User::factory()->student()->create(['email' => 'student@example.com']);

        // Create fees plans for testing
        FeesPlan::create([
            'name' => 'Monthly Plan',
            'description' => 'Monthly subscription plan',
            'amount' => 100,
            'currency' => 'SAR',
            'billing_period' => 'monthly',
            'duration_months' => 1,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_payment()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => 100.00,
                'currency' => 'SAR',
                'payment_method' => 'cash',
                'status' => 'completed',
                'transaction_id' => 'TXN123456',
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'completed_date' => now()->addDays(31)->format('Y-m-d H:i:s'),
                'notes' => 'دفع نقدي',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment' => [
                        'id',
                        'amount',
                        'currency',
                        'payment_method',
                        'status',
                        'transaction_id',
                        'due_date',
                        'paid_date',
                        'notes',
                        'is_successful',
                        'is_pending',
                        'is_failed',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'payment' => [
                        'amount' => 100.00,
                        'currency' => 'SAR',
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'transaction_id' => 'TXN123456',
                        'notes' => 'دفع نقدي',
                        'is_successful' => true,
                        'is_pending' => false,
                        'is_failed' => false,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'student_id' => $this->studentUser->id,
            'amount' => 100.00,
            'currency' => 'SAR',
            'payment_method' => 'cash',
            'status' => 'completed',
            'transaction_id' => 'TXN123456',
        ]);
    }

    /** @test */
    public function admin_can_get_student_payments()
    {
        // Create some payments
        Payment::factory()->create([
            'student_id' => $this->studentUser->id,
            'amount' => 100.00,
            'status' => 'completed',
        ]);
        Payment::factory()->create([
            'student_id' => $this->studentUser->id,
            'amount' => 50.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/payments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'payments',
                    'total_payments',
                    'total_amount',
                    'successful_payments',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'student' => ['id' => $this->studentUser->id],
                    'total_payments' => 2,
                    'total_amount' => 150.00,
                    'successful_payments' => 1,
                ],
            ]);
    }

    /** @test */
    public function teacher_can_get_student_payments()
    {
        Payment::factory()->create(['student_id' => $this->studentUser->id]);

        $this->actingAs($this->teacherUser);
        $token = $this->teacherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/payments");

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_cannot_create_payment()
    {
        $this->actingAs($this->teacherUser);
        $token = $this->teacherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => 100.00,
                'payment_method' => 'cash',
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function student_can_get_own_payments()
    {
        Payment::factory()->create(['student_id' => $this->studentUser->id]);

        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/payments");

        $response->assertStatus(200);
    }

    /** @test */
    public function student_cannot_create_payment()
    {
        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => 100.00,
                'payment_method' => 'cash',
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_validation_errors()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // Missing required fields
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method', 'due_date']);

        // Invalid amount
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => -10,
                'payment_method' => 'cash',
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Invalid payment method
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => 100.00,
                'payment_method' => 'invalid_method',
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    /** @test */
    public function payment_extends_subscription_when_completed()
    {
        // Create subscription
        $subscription = Subscription::factory()->create([
            'student_id' => $this->studentUser->id,
            'end_date' => now()->addDays(10),
        ]);

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/v1/students/{$this->studentUser->id}/payments", [
                'amount' => 100.00,
                'payment_method' => 'cash',
                'status' => 'completed',
                'subscription_id' => $subscription->id,
                'due_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'completed_date' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);

        // Check that subscription was extended
        $subscription->refresh();
        $this->assertTrue($subscription->end_date->greaterThan(now()->addDays(10)));
    }
}
