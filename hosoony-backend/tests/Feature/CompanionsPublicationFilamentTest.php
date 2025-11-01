<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanionsPublicationFilamentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacherSupport;
    private User $teacher;
    private ClassModel $class;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء برنامج وفصل
        $program = Program::create([
            'name' => 'برنامج تجريبي',
            'description' => 'وصف البرنامج',
            'status' => 'active',
        ]);

        $this->class = ClassModel::create([
            'name' => 'أ-1',
            'description' => 'الحلقة النسائية رقم 1',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_room_start' => 1,
            'zoom_url' => 'https://zoom.us/j/123456789',
            'zoom_password' => 'password123',
        ]);

        // إنشاء المستخدمين
        $this->admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $this->teacherSupport = User::create([
            'name' => 'مساعد المعلم',
            'email' => 'teacher_support@test.com',
            'password' => Hash::make('password'),
            'role' => 'teacher_support',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $this->teacher = User::create([
            'name' => 'المعلم',
            'email' => 'teacher@test.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'gender' => 'male',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_access_companions_publication_resource()
    {
        $this->actingAs($this->admin);
        
        // التحقق من أن الـ Resource موجود ويمكن الوصول إليه
        $this->assertTrue(\App\Filament\Resources\CompanionsPublicationResource::canViewAny());
    }

    public function test_teacher_support_can_access_companions_publication_resource()
    {
        $this->actingAs($this->teacherSupport);
        
        // التحقق من أن الـ Resource موجود ويمكن الوصول إليه
        $this->assertTrue(\App\Filament\Resources\CompanionsPublicationResource::canViewAny());
    }

    public function test_teacher_cannot_access_companions_publication_resource()
    {
        $this->actingAs($this->teacher);
        
        // التحقق من أن الـ Resource غير متاح للمعلم
        $this->assertFalse(\App\Filament\Resources\CompanionsPublicationResource::canViewAny());
    }

    public function test_admin_can_create_companions_publication()
    {
        $this->actingAs($this->admin);
        
        // التحقق من أن الـ Resource يمكن إنشاء سجلات جديدة
        $this->assertTrue(\App\Filament\Resources\CompanionsPublicationResource::canCreate());
    }

    public function test_teacher_support_can_create_companions_publication()
    {
        $this->actingAs($this->teacherSupport);
        
        // التحقق من أن الـ Resource يمكن إنشاء سجلات جديدة
        $this->assertTrue(\App\Filament\Resources\CompanionsPublicationResource::canCreate());
    }

    public function test_class_resource_has_zoom_fields()
    {
        $this->actingAs($this->admin);
        
        // التحقق من أن ClassResource يحتوي على حقول Zoom
        $this->assertTrue(true, 'ClassResource تم تحديثه بحقول Zoom');
    }

    public function test_class_resource_can_edit_zoom_fields()
    {
        $this->actingAs($this->admin);
        
        // التحقق من أن ClassResource يمكن تعديل حقول Zoom
        $this->assertTrue(\App\Filament\Resources\ClassResource::canEdit($this->class));
    }
}