<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationApiTest extends TestCase
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
    }

    /** @test */
    public function admin_can_send_test_notification()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => $this->studentUser->id,
                'type' => 'info',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'push',
                'data' => ['test' => true],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'notification' => [
                        'id',
                        'type',
                        'title',
                        'message',
                        'channel',
                        'status',
                        'sent_at',
                    ],
                    'target_user',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'data' => [
                    'notification' => [
                        'type' => 'info',
                        'title' => 'إشعار تجريبي',
                        'message' => 'هذا إشعار تجريبي للاختبار',
                        'channel' => 'push',
                        'status' => 'failed',
                    ],
                    'target_user' => [
                        'id' => $this->studentUser->id,
                        'name' => $this->studentUser->name,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->studentUser->id,
            'type' => 'info',
            'title' => 'إشعار تجريبي',
            'message' => 'هذا إشعار تجريبي للاختبار',
            'channel' => 'push',
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function teacher_cannot_send_test_notification()
    {
        $this->actingAs($this->teacherUser);
        $token = $this->teacherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => $this->studentUser->id,
                'type' => 'info',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'push',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function student_cannot_send_test_notification()
    {
        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => $this->studentUser->id,
                'type' => 'info',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'push',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_get_own_notifications()
    {
        // Create some notifications
        Notification::factory()->create([
            'user_id' => $this->studentUser->id,
            'type' => 'info',
            'title' => 'إشعار 1',
            'message' => 'رسالة الإشعار الأول',
        ]);
        Notification::factory()->create([
            'user_id' => $this->studentUser->id,
            'type' => 'reminder',
            'title' => 'تذكير بالدفع',
            'message' => 'يرجى دفع الرسوم',
        ]);

        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'notifications',
                    'pagination',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 20,
                        'total' => 2,
                    ],
                ],
            ]);
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->studentUser->id,
            'read_at' => null,
        ]);

        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'notification' => [
                        'id',
                        'read_at',
                        'is_read',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => [
                    'notification' => [
                        'id' => $notification->id,
                        'is_read' => true,
                    ],
                ],
            ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function user_cannot_mark_other_user_notification_as_read()
    {
        $otherUser = User::factory()->student()->create();
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($this->studentUser);
        $token = $this->studentUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    /** @test */
    public function test_notification_validation_errors()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // Missing required fields
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'type', 'title', 'message', 'channel']);

        // Invalid user_id
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => 999,
                'type' => 'info',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'push',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);

        // Invalid type
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => $this->studentUser->id,
                'type' => 'invalid_type',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'push',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Invalid channel
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/notifications/test', [
                'user_id' => $this->studentUser->id,
                'type' => 'info',
                'title' => 'إشعار تجريبي',
                'message' => 'هذا إشعار تجريبي للاختبار',
                'channel' => 'invalid_channel',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);
    }
}
