<?php

namespace Tests\Feature;

use App\Console\Commands\CompanionsAutoPublish;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class CompanionsAutoPublishTest extends TestCase
{
    use RefreshDatabase;

    private ClassModel $class;
    private User $admin;

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

        // إنشاء مدير النظام
        $this->admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'gender' => 'male',
            'status' => 'active',
        ]);

        // إنشاء طالبات للاختبار
        for ($i = 1; $i <= 6; $i++) {
            User::create([
                'name' => "طالبة {$i}",
                'email' => "student{$i}@test.com",
                'password' => Hash::make('password'),
                'role' => 'student',
                'gender' => 'female',
                'class_id' => $this->class->id,
                'status' => 'active',
            ]);
        }
    }

    public function test_auto_publish_with_existing_pairings()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر مع pairings موجودة
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4], [5, 6]],
        ]);

        $this->artisan('companions:autopublish')
            ->expectsOutput('بدء النشر التلقائي للرفيقات...')
            ->expectsOutput("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}")
            ->expectsOutput('تم العثور على 1 نشر مطلوب.')
            ->expectsOutput("تم نشر الرفيقات للفصل: {$this->class->name}")
            ->expectsOutput('تم الانتهاء من النشر التلقائي.')
            ->expectsOutput('نشرات ناجحة: 1')
            ->expectsOutput('أخطاء: 0')
            ->assertExitCode(0);

        $publication->refresh();
        $this->assertTrue($publication->isPublished());
        $this->assertTrue($publication->isAutoPublished());
        $this->assertNotNull($publication->room_assignments);
        $this->assertEquals('https://zoom.us/j/123456789', $publication->zoom_url_snapshot);
        $this->assertEquals('password123', $publication->zoom_password_snapshot);
    }

    public function test_auto_publish_without_pairings_generates_default()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر بدون pairings
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'rotation',
            'attendance_source' => 'committed_only',
            'pairings' => null, // لا توجد pairings
        ]);

        $this->artisan('companions:autopublish')
            ->expectsOutput('بدء النشر التلقائي للرفيقات...')
            ->expectsOutput("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}")
            ->expectsOutput('تم العثور على 1 نشر مطلوب.')
            ->expectsOutput("توليد الرفيقات للفصل: {$this->class->name}")
            ->expectsOutput("تم نشر الرفيقات للفصل: {$this->class->name}")
            ->expectsOutput('تم الانتهاء من النشر التلقائي.')
            ->expectsOutput('نشرات ناجحة: 1')
            ->expectsOutput('أخطاء: 0')
            ->assertExitCode(0);

        $publication->refresh();
        $this->assertTrue($publication->isPublished());
        $this->assertTrue($publication->isAutoPublished());
        $this->assertNotNull($publication->pairings);
        $this->assertNotNull($publication->room_assignments);
        $this->assertEquals('pairs', $publication->grouping);
        $this->assertEquals('rotation', $publication->algorithm);
        $this->assertEquals('committed_only', $publication->attendance_source);
    }

    public function test_auto_publish_skips_already_published()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر منشور مسبقاً
        CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4]],
            'published_at' => now(),
            'published_by' => $this->admin->id,
        ]);

        $this->artisan('companions:autopublish')
            ->expectsOutput('بدء النشر التلقائي للرفيقات...')
            ->expectsOutput("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}")
            ->expectsOutput('لا توجد نشرات مطلوبة لليوم التالي.')
            ->assertExitCode(0);
    }

    public function test_auto_publish_handles_multiple_classes()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء فصل ثاني
        $program = Program::first();
        $class2 = ClassModel::create([
            'name' => 'أ-2',
            'description' => 'الحلقة النسائية رقم 2',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_room_start' => 1,
            'zoom_url' => 'https://zoom.us/j/987654321',
            'zoom_password' => 'password456',
        ]);

        // إنشاء طالبات للفصل الثاني
        for ($i = 7; $i <= 10; $i++) {
            User::create([
                'name' => "طالبة {$i}",
                'email' => "student{$i}@test.com",
                'password' => Hash::make('password'),
                'role' => 'student',
                'gender' => 'female',
                'class_id' => $class2->id,
                'status' => 'active',
            ]);
        }

        // إنشاء نشرات للفصلين
        CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4], [5, 6]],
        ]);

        CompanionsPublication::create([
            'class_id' => $class2->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[7, 8], [9, 10]],
        ]);

        $this->artisan('companions:autopublish')
            ->expectsOutput('بدء النشر التلقائي للرفيقات...')
            ->expectsOutput("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}")
            ->expectsOutput('تم العثور على 2 نشر مطلوب.')
            ->expectsOutput("تم نشر الرفيقات للفصل: {$this->class->name}")
            ->expectsOutput("تم نشر الرفيقات للفصل: {$class2->name}")
            ->expectsOutput('تم الانتهاء من النشر التلقائي.')
            ->expectsOutput('نشرات ناجحة: 2')
            ->expectsOutput('أخطاء: 0')
            ->assertExitCode(0);

        // التحقق من نشر الفصلين
        $publication1 = CompanionsPublication::where('class_id', $this->class->id)->first();
        $publication2 = CompanionsPublication::where('class_id', $class2->id)->first();

        $this->assertTrue($publication1->isPublished());
        $this->assertTrue($publication1->isAutoPublished());
        $this->assertTrue($publication2->isPublished());
        $this->assertTrue($publication2->isAutoPublished());
    }

    public function test_auto_publish_handles_errors_gracefully()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء فصل بدون طالبات (لإنتاج خطأ في التوليد)
        $program = Program::first();
        $emptyClass = ClassModel::create([
            'name' => 'أ-فارغ',
            'description' => 'فصل فارغ',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_room_start' => 1,
        ]);

        // إنشاء نشر للفصل الفارغ
        CompanionsPublication::create([
            'class_id' => $emptyClass->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => null, // سيحاول توليد pairings للفصل الفارغ
        ]);

        $this->artisan('companions:autopublish')
            ->expectsOutput('بدء النشر التلقائي للرفيقات...')
            ->expectsOutput("البحث عن النشرات المطلوبة لليوم: {$tomorrow->format('Y-m-d')}")
            ->expectsOutput('تم العثور على 1 نشر مطلوب.')
            ->expectsOutput("توليد الرفيقات للفصل: {$emptyClass->name}")
            ->expectsOutput('تم الانتهاء من النشر التلقائي.')
            ->assertExitCode(0);
    }
}