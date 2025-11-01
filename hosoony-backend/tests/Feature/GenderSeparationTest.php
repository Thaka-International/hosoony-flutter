<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\Session;
use App\Policies\GenderSeparationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GenderSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_male_teacher_cannot_access_female_classes()
    {
        // Create a male teacher
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
            'email' => 'male.teacher@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create a program and female class
        $program = Program::factory()->create();
        $femaleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'female',
            'name' => 'Female Class',
        ]);

        // Login as male teacher
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'male.teacher@test.com',
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Act as the male teacher
        $this->actingAs($maleTeacher);

        // Try to access female class - should be filtered out by scope
        $classes = ClassModel::all();
        $this->assertCount(0, $classes);

        // Try to access female class sessions - should be filtered out
        $sessions = Session::all();
        $this->assertCount(0, $sessions);
    }

    public function test_female_teacher_cannot_access_male_classes()
    {
        // Create a female teacher
        $femaleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'female',
            'email' => 'female.teacher@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create a program and male class
        $program = Program::factory()->create();
        $maleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'male',
            'name' => 'Male Class',
        ]);

        // Login as female teacher
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'female.teacher@test.com',
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Act as the female teacher
        $this->actingAs($femaleTeacher);

        // Try to access male class - should be filtered out by scope
        $classes = ClassModel::all();
        $this->assertCount(0, $classes);
    }

    public function test_admin_can_access_all_classes()
    {
        // Create an admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'gender' => 'male',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create classes of both genders
        $program = Program::factory()->create();
        $maleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'male',
            'name' => 'Male Class',
        ]);
        $femaleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'female',
            'name' => 'Female Class',
        ]);

        // Login as admin
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $token = $response->json('token');

        // Admin should see all classes (scope is bypassed for admin)
        $classes = ClassModel::all();
        $this->assertCount(2, $classes);
    }

    public function test_policy_prevents_assigning_student_to_wrong_gender_class()
    {
        $policy = new GenderSeparationPolicy();

        // Create users
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
        ]);

        $femaleStudent = User::factory()->create([
            'role' => 'student',
            'gender' => 'female',
        ]);

        $maleClass = ClassModel::factory()->create([
            'gender' => 'male',
        ]);

        // Policy should prevent male teacher from assigning female student to male class
        $result = $policy->assignStudentToClass($maleTeacher, $maleClass, $femaleStudent);
        $this->assertFalse($result);
    }

    public function test_policy_allows_assigning_student_to_correct_gender_class()
    {
        $policy = new GenderSeparationPolicy();

        // Create users
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
        ]);

        $maleStudent = User::factory()->create([
            'role' => 'student',
            'gender' => 'male',
        ]);

        $maleClass = ClassModel::factory()->create([
            'gender' => 'male',
        ]);

        // Policy should allow male teacher to assign male student to male class
        $result = $policy->assignStudentToClass($maleTeacher, $maleClass, $maleStudent);
        $this->assertTrue($result);
    }

    public function test_policy_allows_admin_to_assign_anyone()
    {
        $policy = new GenderSeparationPolicy();

        // Create admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'gender' => 'male',
        ]);

        $femaleStudent = User::factory()->create([
            'role' => 'student',
            'gender' => 'female',
        ]);

        $maleClass = ClassModel::factory()->create([
            'gender' => 'male',
        ]);

        // Policy should allow admin to assign anyone
        $result = $policy->assignStudentToClass($admin, $maleClass, $femaleStudent);
        $this->assertTrue($result);
    }

    public function test_policy_prevents_viewing_wrong_gender_class()
    {
        $policy = new GenderSeparationPolicy();

        // Create users
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
        ]);

        $femaleClass = ClassModel::factory()->create([
            'gender' => 'female',
        ]);

        // Policy should prevent male teacher from viewing female class
        $result = $policy->viewClass($maleTeacher, $femaleClass);
        $this->assertFalse($result);
    }

    public function test_policy_allows_viewing_correct_gender_class()
    {
        $policy = new GenderSeparationPolicy();

        // Create users
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
        ]);

        $maleClass = ClassModel::factory()->create([
            'gender' => 'male',
        ]);

        // Policy should allow male teacher to view male class
        $result = $policy->viewClass($maleTeacher, $maleClass);
        $this->assertTrue($result);
    }

    public function test_policy_allows_admin_to_view_any_class()
    {
        $policy = new GenderSeparationPolicy();

        // Create admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'gender' => 'male',
        ]);

        $femaleClass = ClassModel::factory()->create([
            'gender' => 'female',
        ]);

        // Policy should allow admin to view any class
        $result = $policy->viewClass($admin, $femaleClass);
        $this->assertTrue($result);
    }

    public function test_scope_filters_sessions_by_class_gender()
    {
        // Create a male teacher
        $maleTeacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
        ]);

        // Create classes of both genders
        $program = Program::factory()->create();
        $maleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'male',
        ]);
        $femaleClass = ClassModel::factory()->create([
            'program_id' => $program->id,
            'gender' => 'female',
        ]);

        // Create sessions for both classes
        Session::factory()->create([
            'class_id' => $maleClass->id,
            'teacher_id' => $maleTeacher->id,
        ]);
        Session::factory()->create([
            'class_id' => $femaleClass->id,
            'teacher_id' => $maleTeacher->id,
        ]);

        // Login as male teacher
        $this->actingAs($maleTeacher);

        // Should only see sessions from male class
        $sessions = Session::all();
        $this->assertCount(1, $sessions);
        $this->assertEquals('male', $sessions->first()->class->gender);
    }
}
