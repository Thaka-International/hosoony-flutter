<?php

namespace Tests\Unit;

use App\Models\ClassModel;
use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\DailyTaskDefinition;
use App\Models\PerformanceMonthly;
use App\Models\User;
use App\Services\ReportsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportsService $reportsService;
    protected ClassModel $maleClass;
    protected DailyTaskDefinition $hifzTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportsService = new ReportsService();
        
        // Create class
        $this->maleClass = ClassModel::factory()->create(['gender' => 'male']);
        
        // Create daily task definition
        $this->hifzTask = DailyTaskDefinition::factory()->create([
            'name' => 'hifz',
            'description' => 'حفظ القرآن الكريم',
            'points_weight' => 20,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        // Ensure migrations are run
        $this->artisan('migrate');
    }

    /** @test */
    public function can_generate_daily_report_data()
    {
        // Create students
        $student1 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        // Create daily logs
        $log1 = DailyLog::create([
            'student_id' => $student1->id,
            'log_date' => '2024-01-01',
            'status' => 'verified',
            'finish_order' => 1,
        ]);

        $log2 = DailyLog::create([
            'student_id' => $student2->id,
            'log_date' => '2024-01-01',
            'status' => 'verified',
            'finish_order' => 2,
        ]);

        // Create log items
        DailyLogItem::create([
            'daily_log_id' => $log1->id,
            'task_definition_id' => $this->hifzTask->id,
            'status' => 'completed',
            'proof_type' => 'none',
            'notes' => 'حفظت صفحة واحدة',
        ]);

        DailyLogItem::create([
            'daily_log_id' => $log2->id,
            'task_definition_id' => $this->hifzTask->id,
            'status' => 'completed',
            'proof_type' => 'none',
        ]);

        $reportData = $this->reportsService->generateDailyReport($this->maleClass->id, '2024-01-01');

        $this->assertArrayHasKey('class', $reportData);
        $this->assertArrayHasKey('date', $reportData);
        $this->assertArrayHasKey('hijri_date', $reportData);
        $this->assertArrayHasKey('students', $reportData);
        $this->assertArrayHasKey('summary', $reportData);

        $this->assertEquals($this->maleClass->id, $reportData['class']->id);
        $this->assertEquals('2024-01-01', $reportData['date']->format('Y-m-d'));
        $this->assertIsString($reportData['hijri_date']);

        $this->assertEquals(2, $reportData['summary']['total_students']);
        $this->assertEquals(2, $reportData['summary']['completed_students']);
        $this->assertEquals(100.0, $reportData['summary']['completion_rate']);

        $this->assertCount(2, $reportData['students']);
        
        // Check first student data
        $firstStudent = $reportData['students'][0];
        $this->assertEquals($student1->id, $firstStudent['student']->id);
        $this->assertEquals(1, $firstStudent['finish_order']);
        $this->assertEquals(1, $firstStudent['completed_tasks']);
        $this->assertEquals(1, $firstStudent['total_tasks']);
        $this->assertEquals(100.0, $firstStudent['completion_rate']);
        $this->assertContains('حفظت صفحة واحدة', $firstStudent['notes']);
    }

    /** @test */
    public function can_generate_monthly_report_data()
    {
        // Create students
        $student1 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        // Create monthly performance records
        PerformanceMonthly::create([
            'student_id' => $student1->id,
            'year' => 2024,
            'month' => 1,
            'total_points' => 500,
            'rank' => 1,
        ]);

        PerformanceMonthly::create([
            'student_id' => $student2->id,
            'year' => 2024,
            'month' => 1,
            'total_points' => 450,
            'rank' => 2,
        ]);

        $reportData = $this->reportsService->generateMonthlyReport($this->maleClass->id, 1, 2024, 6, 1445);

        $this->assertArrayHasKey('class', $reportData);
        $this->assertArrayHasKey('month', $reportData);
        $this->assertArrayHasKey('year', $reportData);
        $this->assertArrayHasKey('hijri_month', $reportData);
        $this->assertArrayHasKey('hijri_year', $reportData);
        $this->assertArrayHasKey('month_name', $reportData);
        $this->assertArrayHasKey('hijri_month_name', $reportData);
        $this->assertArrayHasKey('students', $reportData);
        $this->assertArrayHasKey('summary', $reportData);

        $this->assertEquals($this->maleClass->id, $reportData['class']->id);
        $this->assertEquals(1, $reportData['month']);
        $this->assertEquals(2024, $reportData['year']);
        $this->assertEquals(6, $reportData['hijri_month']);
        $this->assertEquals(1445, $reportData['hijri_year']);
        $this->assertEquals('January 2024', $reportData['month_name']);
        $this->assertEquals('جمادى الأولى 1445', $reportData['hijri_month_name']);

        $this->assertEquals(2, $reportData['summary']['total_students']);
        $this->assertEquals(475.0, $reportData['summary']['average_points']);

        $this->assertCount(2, $reportData['students']);
        
        // Check first student data (sorted by rank)
        $firstStudent = $reportData['students'][0];
        $this->assertEquals($student1->id, $firstStudent['student']->id);
        $this->assertEquals(500, $firstStudent['total_points']);
        $this->assertEquals(1, $firstStudent['rank']);
    }

    /** @test */
    public function can_export_daily_report_as_csv()
    {
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        // Create daily log
        $log = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => '2024-01-01',
            'status' => 'verified',
            'finish_order' => 1,
        ]);

        // Create log item
        DailyLogItem::create([
            'daily_log_id' => $log->id,
            'task_definition_id' => $this->hifzTask->id,
            'status' => 'completed',
            'proof_type' => 'none',
            'notes' => 'حفظت صفحة واحدة',
        ]);

        $reportData = $this->reportsService->generateDailyReport($this->maleClass->id, '2024-01-01');
        $csv = $this->reportsService->exportDailyReportAsCsv($reportData);

        $this->assertStringContainsString('اسم الطالب,ترتيب الإنجاز,المهام المكتملة', $csv);
        $this->assertStringContainsString($student->name, $csv);
        $this->assertStringContainsString('1,1,1,100.0%,محقق', $csv);
        $this->assertStringContainsString('حفظت صفحة واحدة', $csv);
    }

    /** @test */
    public function can_export_monthly_report_as_csv()
    {
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        // Create monthly performance record
        PerformanceMonthly::create([
            'student_id' => $student->id,
            'year' => 2024,
            'month' => 1,
            'total_points' => 500,
            'rank' => 1,
        ]);

        $reportData = $this->reportsService->generateMonthlyReport($this->maleClass->id, 1, 2024);
        $csv = $this->reportsService->exportMonthlyReportAsCsv($reportData);

        $this->assertStringContainsString('اسم الطالب,الترتيب,إجمالي النقاط,أيام الحضور', $csv);
        $this->assertStringContainsString($student->name, $csv);
        $this->assertStringContainsString('1,500,0,31,0.0%', $csv);
    }

    /** @test */
    public function can_store_and_retrieve_monthly_report()
    {
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        // Create monthly performance record
        PerformanceMonthly::create([
            'student_id' => $student->id,
            'year' => 2024,
            'month' => 1,
            'total_points' => 500,
            'rank' => 1,
        ]);

        $reportData = $this->reportsService->generateMonthlyReport($this->maleClass->id, 1, 2024);
        $filepath = $this->reportsService->storeMonthlyReport($reportData);

        $this->assertStringContainsString('monthly_report_', $filepath);
        $this->assertStringContainsString('2024_1.json', $filepath);

        $retrievedData = $this->reportsService->getStoredMonthlyReport($filepath);
        
        $this->assertEquals($reportData['class']->id, $retrievedData['class']['id']);
        $this->assertEquals($reportData['month'], $retrievedData['month']);
        $this->assertEquals($reportData['year'], $retrievedData['year']);
        $this->assertEquals($reportData['summary']['total_students'], $retrievedData['summary']['total_students']);
    }

    /** @test */
    public function daily_report_handles_students_without_logs()
    {
        // Create students without daily logs
        $student1 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        $reportData = $this->reportsService->generateDailyReport($this->maleClass->id, '2024-01-01');

        $this->assertEquals(2, $reportData['summary']['total_students']);
        $this->assertEquals(0, $reportData['summary']['completed_students']);
        $this->assertEquals(0.0, $reportData['summary']['completion_rate']);

        $this->assertCount(2, $reportData['students']);
        
        foreach ($reportData['students'] as $studentData) {
            $this->assertNull($studentData['finish_order']);
            $this->assertEquals(0, $studentData['completed_tasks']);
            $this->assertEquals(0, $studentData['total_tasks']);
            $this->assertEquals(0.0, $studentData['completion_rate']);
            $this->assertEquals('not_submitted', $studentData['status']);
        }
    }

    /** @test */
    public function monthly_report_handles_students_without_performance()
    {
        // Create students without monthly performance
        $student1 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);

        $reportData = $this->reportsService->generateMonthlyReport($this->maleClass->id, 1, 2024);

        $this->assertEquals(2, $reportData['summary']['total_students']);
        $this->assertEquals(0.0, $reportData['summary']['average_points']);

        $this->assertCount(2, $reportData['students']);
        
        foreach ($reportData['students'] as $studentData) {
            $this->assertNull($studentData['performance']);
            $this->assertEquals(0, $studentData['total_points']);
            $this->assertNull($studentData['rank']);
        }
    }
}


