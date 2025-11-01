<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\DailyTaskDefinition;
use App\Models\PerformanceMonthly;
use App\Models\User;
use App\Services\ReportsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $teacherUser;
    protected ClassModel $maleClass;
    protected ClassModel $femaleClass;
    protected DailyTaskDefinition $hifzTask;
    protected DailyTaskDefinition $murajaahTask;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::findOrCreate('admin');
        Role::findOrCreate('teacher');
        Role::findOrCreate('student');

        // Create users
        $this->adminUser = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $this->teacherUser = User::factory()->teacher()->male()->create(['email' => 'teacher@example.com']);

        // Create classes
        $this->maleClass = ClassModel::factory()->create(['gender' => 'male']);
        $this->femaleClass = ClassModel::factory()->create(['gender' => 'female']);

        // Create daily task definitions
        $this->hifzTask = DailyTaskDefinition::factory()->create([
            'name' => 'hifz',
            'description' => 'حفظ القرآن الكريم',
            'points_weight' => 20,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
        $this->murajaahTask = DailyTaskDefinition::factory()->create([
            'name' => 'murajaah',
            'description' => 'مراجعة القرآن الكريم',
            'points_weight' => 20,
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        // Ensure migrations are run
        $this->artisan('migrate');
    }

    /** @test */
    public function can_generate_daily_report_json()
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

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}?date=2024-01-01");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'class',
                    'date',
                    'hijri_date',
                    'students' => [
                        '*' => [
                            'student',
                            'daily_log',
                            'finish_order',
                            'completed_tasks',
                            'total_tasks',
                            'completion_rate',
                            'notes',
                            'status',
                        ],
                    ],
                    'summary' => [
                        'total_students',
                        'completed_students',
                        'completion_rate',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'class' => [
                        'id' => $this->maleClass->id,
                        'name' => $this->maleClass->name,
                    ],
                    'summary' => [
                        'total_students' => 2,
                        'completed_students' => 2,
                        'completion_rate' => 100.0,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_generate_daily_report_csv()
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

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}?date=2024-01-01&export=csv");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="daily_report_' . $this->maleClass->id . '_2024-01-01.csv"');

        $csvContent = $response->getContent();
        $this->assertStringContainsString('اسم الطالب,ترتيب الإنجاز,المهام المكتملة', $csvContent);
        $this->assertStringContainsString($student->name, $csvContent);
        $this->assertStringContainsString('1,1,1,100.0%', $csvContent);
    }

    /** @test */
    public function can_generate_monthly_report()
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

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'class_id' => $this->maleClass->id,
                'month' => 1,
                'year' => 2024,
                'hijri_month' => 6,
                'hijri_year' => 1445,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'report_id',
                    'class_name',
                    'month_name',
                    'hijri_month_name',
                    'summary' => [
                        'total_students',
                        'average_attendance',
                        'average_points',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Monthly report generated successfully',
                'data' => [
                    'class_name' => $this->maleClass->name,
                    'month_name' => 'January 2024',
                    'hijri_month_name' => 'جمادى الأولى 1445',
                    'summary' => [
                        'total_students' => 2,
                        'average_points' => 475,
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_export_monthly_report_pdf()
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

        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // First generate the report
        $generateResponse = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'class_id' => $this->maleClass->id,
                'month' => 1,
                'year' => 2024,
            ]);

        $reportId = $generateResponse->json('data.report_id');

        // Then export as PDF
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get("/api/v1/reports/monthly/{$reportId}/export");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function validation_errors_for_daily_report()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // Missing date
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}");
        $response->assertStatus(422);

        // Invalid date format
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}?date=invalid-date");
        $response->assertStatus(422);

        // Invalid export format
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}?date=2024-01-01&export=invalid");
        $response->assertStatus(422);
    }

    /** @test */
    public function validation_errors_for_monthly_report()
    {
        $this->actingAs($this->adminUser);
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        // Missing class_id
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'month' => 1,
                'year' => 2024,
            ]);
        $response->assertStatus(422);

        // Invalid month
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'class_id' => $this->maleClass->id,
                'month' => 13,
                'year' => 2024,
            ]);
        $response->assertStatus(422);

        // Invalid year
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'class_id' => $this->maleClass->id,
                'month' => 1,
                'year' => 2019,
            ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function unauthorized_access_to_reports()
    {
        $student = User::factory()->student()->male()->create(['class_id' => $this->maleClass->id]);
        $this->actingAs($student);
        $token = $student->createToken('test-token')->plainTextToken;

        // Student cannot access reports
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/reports/daily/{$this->maleClass->id}?date=2024-01-01");
        $response->assertStatus(403);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/reports/monthly/generate', [
                'class_id' => $this->maleClass->id,
                'month' => 1,
                'year' => 2024,
            ]);
        $response->assertStatus(403);
    }
}
