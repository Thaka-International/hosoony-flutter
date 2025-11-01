<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $teacherRole = Role::create(['name' => 'teacher']);
        $teacherSupportRole = Role::create(['name' => 'teacher_support']);
        $studentRole = Role::create(['name' => 'student']);

        // Create permissions
        $permissions = [
            'manage_users',
            'manage_courses',
            'manage_students',
            'view_reports',
            'manage_settings',
            'view_dashboard',
            'manage_assignments',
            'grade_assignments',
            'view_student_progress',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo($permissions);
        $teacherRole->givePermissionTo([
            'manage_courses',
            'manage_students',
            'view_reports',
            'view_dashboard',
            'manage_assignments',
            'grade_assignments',
            'view_student_progress',
        ]);
        $teacherSupportRole->givePermissionTo([
            'view_reports',
            'view_dashboard',
            'view_student_progress',
        ]);
        $studentRole->givePermissionTo([
            'view_dashboard',
        ]);

        // Create 1 admin
        $admin = User::factory()->admin()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@hosoony.com',
            'phone' => '+966501234567',
        ]);
        $admin->assignRole('admin');

        // Create 2 teachers (1 male, 1 female)
        $maleTeacher = User::factory()->teacher()->male()->create([
            'name' => 'أحمد محمد',
            'email' => 'ahmed@hosoony.com',
            'phone' => '+966501234568',
        ]);
        $maleTeacher->assignRole('teacher');

        $femaleTeacher = User::factory()->teacher()->female()->create([
            'name' => 'فاطمة أحمد',
            'email' => 'fatima@hosoony.com',
            'phone' => '+966501234569',
        ]);
        $femaleTeacher->assignRole('teacher');

        // Create 2 teacher supports (1 male, 1 female)
        $maleSupport = User::factory()->teacherSupport()->male()->create([
            'name' => 'خالد عبدالله',
            'email' => 'khalid@hosoony.com',
            'phone' => '+966501234570',
        ]);
        $maleSupport->assignRole('teacher_support');

        $femaleSupport = User::factory()->teacherSupport()->female()->create([
            'name' => 'نورا سعد',
            'email' => 'nora@hosoony.com',
            'phone' => '+966501234571',
        ]);
        $femaleSupport->assignRole('teacher_support');

        // Create 10 students (5 male, 5 female)
        $maleStudents = User::factory()->student()->male()->count(5)->create([
            'guardian_name' => 'والد الطالب',
            'guardian_phone' => '+966501234572',
        ]);
        foreach ($maleStudents as $student) {
            $student->assignRole('student');
        }

        $femaleStudents = User::factory()->student()->female()->count(5)->create([
            'guardian_name' => 'والدة الطالبة',
            'guardian_phone' => '+966501234573',
        ]);
        foreach ($femaleStudents as $student) {
            $student->assignRole('student');
        }
    }
}
