<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use App\Policies\GenderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user creation with different roles.
     */
    public function testCanCreateUsersWithDifferentRoles(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $teacherRole = Role::create(['name' => 'teacher']);
        $studentRole = Role::create(['name' => 'student']);

        // Create admin user
        $admin = User::factory()->admin()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertEquals('admin', $admin->role);

        // Create teacher user
        $teacher = User::factory()->teacher()->create();
        $teacher->assignRole('teacher');

        $this->assertTrue($teacher->isTeacher());
        $this->assertTrue($teacher->isActive());
        $this->assertEquals('teacher', $teacher->role);

        // Create student user
        $student = User::factory()->student()->create();
        $student->assignRole('student');

        $this->assertTrue($student->isStudent());
        $this->assertTrue($student->isActive());
        $this->assertEquals('student', $student->role);
    }

    /**
     * Test user permissions assignment.
     */
    public function testCanAssignPermissionsToUsers(): void
    {
        // Create permission
        $permission = Permission::create(['name' => 'manage_users']);

        // Create role with permission
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo('manage_users');

        // Create user and assign role
        $user = User::factory()->admin()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermissionTo('manage_users'));
        $this->assertTrue($user->can('manage_users'));
    }

    /**
     * Test user gender-specific methods.
     */
    public function testUserGenderSpecificMethods(): void
    {
        $maleUser = User::factory()->male()->create();
        $femaleUser = User::factory()->female()->create();

        $this->assertEquals('male', $maleUser->gender);
        $this->assertEquals('female', $femaleUser->gender);

        // Test full name attribute
        $this->assertStringContainsString('الأستاذ', $maleUser->full_name);
        $this->assertStringContainsString('الأستاذة', $femaleUser->full_name);
    }

    /**
     * Test user-device relationship.
     */
    public function testUserDeviceRelationship(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->devices->contains($device));
        $this->assertEquals($user->id, $device->user_id);
    }

    /**
     * Test device creation and status.
     */
    public function testDeviceCreationAndStatus(): void
    {
        $user = User::factory()->create();
        $device = Device::factory()->active()->create(['user_id' => $user->id]);

        $this->assertTrue($device->isActive());
        $this->assertNotNull($device->last_seen_at);

        // Test inactive device
        $inactiveDevice = Device::factory()->inactive()->create(['user_id' => $user->id]);
        $this->assertFalse($inactiveDevice->isActive());
    }

    /**
     * Test device platform-specific creation.
     */
    public function testDevicePlatformSpecificCreation(): void
    {
        $user = User::factory()->create();

        $webDevice = Device::factory()->web()->create(['user_id' => $user->id]);
        $androidDevice = Device::factory()->android()->create(['user_id' => $user->id]);
        $iosDevice = Device::factory()->ios()->create(['user_id' => $user->id]);

        $this->assertEquals('web', $webDevice->platform);
        $this->assertEquals('android', $androidDevice->platform);
        $this->assertEquals('ios', $iosDevice->platform);

        $this->assertNull($webDevice->fcm_token);
        $this->assertNotNull($androidDevice->fcm_token);
        $this->assertNotNull($iosDevice->fcm_token);
    }

    /**
     * Test gender policy placeholder methods.
     */
    public function testGenderPolicyPlaceholderMethods(): void
    {
        $policy = new GenderPolicy();
        $maleUser = User::factory()->male()->teacher()->create();
        $femaleUser = User::factory()->female()->student()->create();

        // Test all placeholder methods return true for now
        $this->assertTrue($policy->accessMixedGender($maleUser));
        $this->assertTrue($policy->teachOppositeGender($maleUser, $femaleUser));
        $this->assertTrue($policy->groupWithOppositeGender($maleUser, $femaleUser));
        $this->assertTrue($policy->viewOppositeGenderProfile($maleUser, $femaleUser));
        $this->assertTrue($policy->communicateWithOppositeGender($maleUser, $femaleUser));
    }

    /**
     * Test user factory states.
     */
    public function testUserFactoryStates(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $teacherSupport = User::factory()->teacherSupport()->create();
        $student = User::factory()->student()->create();
        $maleUser = User::factory()->male()->create();
        $femaleUser = User::factory()->female()->create();

        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('teacher', $teacher->role);
        $this->assertEquals('teacher_support', $teacherSupport->role);
        $this->assertEquals('student', $student->role);
        $this->assertEquals('male', $maleUser->gender);
        $this->assertEquals('female', $femaleUser->gender);

        // Students should have guardian info
        $this->assertNotNull($student->guardian_name);
        $this->assertNotNull($student->guardian_phone);
    }

    /**
     * Test device factory states.
     */
    public function testDeviceFactoryStates(): void
    {
        $user = User::factory()->create();

        $webDevice = Device::factory()->web()->create(['user_id' => $user->id]);
        $androidDevice = Device::factory()->android()->create(['user_id' => $user->id]);
        $iosDevice = Device::factory()->ios()->create(['user_id' => $user->id]);
        $activeDevice = Device::factory()->active()->create(['user_id' => $user->id]);
        $inactiveDevice = Device::factory()->inactive()->create(['user_id' => $user->id]);

        $this->assertEquals('web', $webDevice->platform);
        $this->assertEquals('android', $androidDevice->platform);
        $this->assertEquals('ios', $iosDevice->platform);
        $this->assertTrue($activeDevice->isActive());
        $this->assertFalse($inactiveDevice->isActive());
    }
}
