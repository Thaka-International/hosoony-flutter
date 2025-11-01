<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\DailyTaskDefinition;
use App\Models\GamificationPoint;
use App\Services\DailyTasksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class DailyTasksApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'student']);
    }

    public function test_get_daily_tasks_for_student()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        DailyTaskDefinition::factory()->create(['name' => 'murajaah', 'points_weight' => 20]);
        
        // Login as student
        $token = $student->createToken('test')->plainTextToken;
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$student->id}/daily-tasks?date=2024-01-01&class_id={$class->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'date',
                'class_id',
                'tasks' => [
                    '*' => [
                        'task_key',
                        'task_name',
                        'points_weight',
                        'duration_minutes',
                        'completed',
                        'proof_type',
                        'notes',
                        'quantity',
                        'duration_minutes',
                    ]
                ],
                'existing_log'
            ]);
    }

    public function test_submit_daily_log_first_student()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        DailyTaskDefinition::factory()->create(['name' => 'murajaah', 'points_weight' => 20]);
        
        // Login as student
        $token = $student->createToken('test')->plainTextToken;
        
        $data = [
            'class_id' => $class->id,
            'log_date' => '2024-01-01',
            'items' => [
                [
                    'task_key' => 'hifz',
                    'completed' => true,
                    'proof_type' => 'none',
                ],
                [
                    'task_key' => 'murajaah',
                    'completed' => true,
                    'proof_type' => 'note',
                    'notes' => 'راجعت 2 وجه',
                ],
            ],
        ];
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/daily-logs/submit', $data);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'daily_log_id',
                'finish_order',
                'points_awarded',
                'message',
            ]);
        
        // Verify finish order is 1 (first student)
        $response->assertJson(['finish_order' => 1]);
        
        // Verify points calculation: 20 (hifz) + 20 (murajaah) + 10 (first place bonus) = 50
        $response->assertJson(['points_awarded' => 50]);
        
        // Verify database records
        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student->id,
            'log_date' => '2024-01-01 00:00:00',
            'finish_order' => 1,
            'status' => 'submitted',
        ]);
        
        $this->assertDatabaseHas('gamification_points', [
            'student_id' => $student->id,
            'source_type' => 'daily_log',
            'points' => 50,
        ]);
    }

    public function test_submit_daily_log_second_student()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create students
        $student1 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        
        // First student submits
        $token1 = $student1->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        // Second student submits
        $token2 = $student2->createToken('test')->plainTextToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        // Verify finish order is 2 (second student)
        $response->assertJson(['finish_order' => 2]);
        
        // Verify points calculation: 20 (hifz) + 6 (second place bonus) = 26
        $response->assertJson(['points_awarded' => 26]);
    }

    public function test_submit_daily_log_third_student()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create students
        $student1 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student3 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        
        // First two students submit
        $token1 = $student1->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $token2 = $student2->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        // Third student submits
        $token3 = $student3->createToken('test')->plainTextToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token3])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        // Verify finish order is 3 (third student)
        $response->assertJson(['finish_order' => 3]);
        
        // Verify points calculation: 20 (hifz) + 3 (third place bonus) = 23
        $response->assertJson(['points_awarded' => 23]);
    }

    public function test_submit_daily_log_fourth_student_no_bonus()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create students
        $student1 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student2 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student3 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        $student4 = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        
        // First three students submit
        $token1 = $student1->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $token2 = $student2->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $token3 = $student3->createToken('test')->plainTextToken;
        $this->withHeaders(['Authorization' => 'Bearer ' . $token3])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        // Fourth student submits
        $token4 = $student4->createToken('test')->plainTextToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token4])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => $class->id,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'hifz', 'completed' => true, 'proof_type' => 'none'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        // Verify finish order is 4 (fourth student)
        $response->assertJson(['finish_order' => 4]);
        
        // Verify points calculation: 20 (hifz) + 0 (no bonus) = 20
        $response->assertJson(['points_awarded' => 20]);
    }

    public function test_get_student_daily_logs()
    {
        // Create program and class
        $program = Program::factory()->create();
        $class = ClassModel::factory()->create(['program_id' => $program->id, 'gender' => 'male']);
        
        // Create student
        $student = User::factory()->student()->male()->create(['class_id' => $class->id]);
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        
        // Create daily log
        $dailyLog = DailyLog::factory()->create([
            'student_id' => $student->id,
            'log_date' => '2024-01-01',
            'finish_order' => 1,
        ]);
        
        DailyLogItem::factory()->create([
            'daily_log_id' => $dailyLog->id,
            'task_definition_id' => DailyTaskDefinition::where('name', 'hifz')->first()->id,
            'status' => 'completed',
        ]);
        
        // Login as student
        $token = $student->createToken('test')->plainTextToken;
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$student->id}/daily-logs?start_date=2024-01-01&end_date=2024-01-31");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'student_id',
                'start_date',
                'end_date',
                'logs' => [
                    '*' => [
                        'id',
                        'log_date',
                        'status',
                        'verified_by',
                        'verified_at',
                        'notes',
                        'items' => [
                            '*' => [
                                'task_key',
                                'task_name',
                                'status',
                                'proof_type',
                                'notes',
                                'quantity',
                                'duration_minutes',
                                'points_weight',
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_unauthorized_access_to_other_student_data()
    {
        // Create students
        $student1 = User::factory()->student()->male()->create();
        $student2 = User::factory()->student()->male()->create();
        
        // Login as student1
        $token = $student1->createToken('test')->plainTextToken;
        
        // Try to access student2's data
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$student2->id}/daily-tasks?date=2024-01-01");
        
        $response->assertStatus(403);
    }

    public function test_admin_can_access_any_student_data()
    {
        // Create admin and student
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->male()->create();
        
        // Create task definitions
        DailyTaskDefinition::factory()->create(['name' => 'hifz', 'points_weight' => 20]);
        
        // Login as admin
        $token = $admin->createToken('test')->plainTextToken;
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/v1/students/{$student->id}/daily-tasks?date=2024-01-01");
        
        $response->assertStatus(200);
    }

    public function test_validation_errors_for_submit_daily_log()
    {
        $student = User::factory()->student()->male()->create();
        $token = $student->createToken('test')->plainTextToken;
        
        // Test missing required fields
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/daily-logs/submit', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['class_id', 'log_date', 'items']);
        
        // Test invalid task_key
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/daily-logs/submit', [
                'class_id' => 1,
                'log_date' => '2024-01-01',
                'items' => [
                    ['task_key' => 'invalid_task', 'completed' => true],
                ],
            ]);
        
        $response->assertStatus(422);
    }
}
