<?php

namespace Tests\Feature;

use App\Models\FeesPlan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
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
    public function admin_can_get_student_subscription()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/subscription");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'subscription',
                    'status',
                    'message',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'student' => ['id' => $this->studentUser->id],
                    'subscription' => null,
                    'status' => 'no_subscription',
                ],
            ]);
    }

    /** @test */
    public function admin_can_update_student_subscription()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $activeUntil = Carbon::now()->addDays(30);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/students/{$this->studentUser->id}/subscription", [
                'end_date' => $activeUntil->format('Y-m-d H:i:s'),
                'status' => 'active',
                'start_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'fees_plan_id' => 1,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'subscription' => [
                        'id',
                        'status',
                        'end_date',
                        'start_date',
                        'is_active',
                        'is_expired',
                        'days_until_expiration',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Subscription updated successfully',
                'data' => [
                    'subscription' => [
                        'status' => 'active',
                        'is_active' => true,
                        'is_expired' => false,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'student_id' => $this->studentUser->id,
            'status' => 'active',
            'fees_plan_id' => 1,
        ]);
    }

    /** @test */
    public function teacher_can_get_student_subscription()
    {
        $this->actingAs($this->teacherUser);
        $token = $this->teacherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/subscription");

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_cannot_update_student_subscription()
    {
        $this->actingAs($this->teacherUser);
        $token = $this->teacherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/students/{$this->studentUser->id}/subscription", [
                'end_date' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
                'status' => 'active',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function student_can_get_own_subscription()
    {
        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$this->studentUser->id}/subscription");

        $response->assertStatus(200);
    }

    /** @test */
    public function student_cannot_get_other_student_subscription()
    {
        $otherStudent = User::factory()->student()->create();
        
        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$otherStudent->id}/subscription");

        $response->assertStatus(403);
    }

    /** @test */
    public function subscription_validation_errors()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // Missing required fields
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/students/{$this->studentUser->id}/subscription", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date', 'status']);

        // Invalid date
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/students/{$this->studentUser->id}/subscription", [
                'end_date' => 'invalid-date',
                'status' => 'active',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);

        // Invalid status
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/students/{$this->studentUser->id}/subscription", [
                'end_date' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
