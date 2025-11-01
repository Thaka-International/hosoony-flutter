<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DailyLogsUniqueConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_logs_unique_constraint_student_id_and_log_date()
    {
        // Create a student
        $student = User::factory()->student()->create();

        // Create first daily log
        $firstLog = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => '2024-01-01',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student->id,
            'log_date' => '2024-01-01 00:00:00',
        ]);

        // Try to create duplicate daily log - should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        DailyLog::create([
            'student_id' => $student->id,
            'log_date' => '2024-01-01', // Same date
            'status' => 'verified',
        ]);
    }

    public function test_daily_logs_allows_different_students_same_date()
    {
        // Create two students
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();

        // Create daily logs for same date but different students
        $log1 = DailyLog::create([
            'student_id' => $student1->id,
            'log_date' => '2024-01-01',
            'status' => 'pending',
        ]);

        $log2 = DailyLog::create([
            'student_id' => $student2->id,
            'log_date' => '2024-01-01',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student1->id,
            'log_date' => '2024-01-01 00:00:00',
        ]);

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student2->id,
            'log_date' => '2024-01-01 00:00:00',
        ]);
    }

    public function test_daily_logs_allows_same_student_different_dates()
    {
        // Create a student
        $student = User::factory()->student()->create();

        // Create daily logs for different dates
        $log1 = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => '2024-01-01',
            'status' => 'pending',
        ]);

        $log2 = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => '2024-01-02',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student->id,
            'log_date' => '2024-01-01 00:00:00',
        ]);

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student->id,
            'log_date' => '2024-01-02 00:00:00',
        ]);
    }

    public function test_daily_logs_basic_filters()
    {
        // Create students and daily logs
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();

        DailyLog::create([
            'student_id' => $student1->id,
            'log_date' => '2024-01-01',
            'status' => 'pending',
        ]);

        DailyLog::create([
            'student_id' => $student2->id,
            'log_date' => '2024-01-01',
            'status' => 'verified',
        ]);

        DailyLog::create([
            'student_id' => $student1->id,
            'log_date' => '2024-01-02',
            'status' => 'verified',
        ]);

        // Test filtering by student
        $student1Logs = DailyLog::where('student_id', $student1->id)->get();
        $this->assertCount(2, $student1Logs);

        // Test filtering by status
        $pendingLogs = DailyLog::where('status', 'pending')->get();
        $this->assertCount(1, $pendingLogs);

        $verifiedLogs = DailyLog::where('status', 'verified')->get();
        $this->assertCount(2, $verifiedLogs);

        // Test filtering by date range
        $jan1Logs = DailyLog::whereDate('log_date', '2024-01-01')->get();
        $this->assertCount(2, $jan1Logs);

        $jan2Logs = DailyLog::whereDate('log_date', '2024-01-02')->get();
        $this->assertCount(1, $jan2Logs);
    }
}
