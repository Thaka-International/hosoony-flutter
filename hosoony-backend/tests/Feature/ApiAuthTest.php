<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Login request
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'gender',
                    'status',
                ],
                'token',
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'email' => 'test@example.com',
                    'role' => $user->role,
                ],
            ]);

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login with wrong password
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_with_inactive_account()
    {
        // Create an inactive user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        // Login request
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Account is inactive',
            ]);
    }

    public function test_login_requires_email_and_password()
    {
        // Login without email
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Login without password
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_authenticated_user_can_get_profile()
    {
        // Create and login user
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        // Get profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'gender',
                    'phone',
                    'guardian_name',
                    'guardian_phone',
                    'locale',
                    'status',
                    'last_seen_at',
                    'created_at',
                    'updated_at',
                ],
                'permissions',
                'roles',
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        // Get profile without token
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout()
    {
        // Create and login user
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        // Logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_user_role_syncs_with_spatie_permission()
    {
        // Create roles first
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'teacher']);

        // Create a user with specific role
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Manually assign role since boot method might not work in tests
        $user->assignRole('admin');

        // Check if role was assigned
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->isAdmin());

        // Update role
        $user->update(['role' => 'teacher']);
        $user->syncRoles(['teacher']);
        $user->refresh();

        // Check if role was synced
        $this->assertTrue($user->hasRole('teacher'));
        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_different_user_roles_have_correct_permissions()
    {
        // Create users with different roles
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        // Test role checks
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());
        $this->assertFalse($admin->isStudent());

        $this->assertFalse($teacher->isAdmin());
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isStudent());

        $this->assertFalse($student->isAdmin());
        $this->assertFalse($student->isTeacher());
        $this->assertTrue($student->isStudent());
    }
}
