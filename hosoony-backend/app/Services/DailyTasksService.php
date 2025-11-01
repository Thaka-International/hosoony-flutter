<?php

namespace App\Services;

use App\Models\DailyLog;
use App\Models\DailyLogItem;
use App\Models\DailyTaskDefinition;
use App\Models\GamificationPoint;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\WeeklyTaskSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyTasksService
{
    /**
     * Get daily tasks for a student
     */
    public function getDailyTasks(int $studentId, string $date, ?int $classId = null): array
    {
        $logDate = Carbon::parse($date);
        
        // Get student's class if not provided
        if (!$classId) {
            $student = User::findOrFail($studentId);
            $classId = $student->class_id ?? null;
        }

        if (!$classId) {
            return [
                'date' => $logDate->format('Y-m-d'),
                'class_id' => null,
                'tasks' => [],
                'existing_log' => null,
                'message' => 'الطالب غير مرتبط بأي فصل'
            ];
        }

        // Convert date to day of week (Carbon format: 0 = Sunday, 6 = Saturday)
        $dayOfWeekIndex = (int) $logDate->format('w'); // 0-6
        $dayOfWeekMap = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];
        $dayOfWeek = $dayOfWeekMap[$dayOfWeekIndex] ?? null;

        // Check if there's an active schedule for this day
        if ($dayOfWeek) {
            $hasSchedule = ClassSchedule::where('class_id', $classId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->exists();

            if (!$hasSchedule) {
                // No schedule for this day - return empty tasks
                return [
                    'date' => $logDate->format('Y-m-d'),
                    'class_id' => $classId,
                    'tasks' => [],
                    'existing_log' => null,
                    'message' => 'لا يوجد جدول للفصل في هذا اليوم'
                ];
            }
        }

        // Get task definitions assigned to the class
        $taskAssignments = ClassModel::findOrFail($classId)
            ->activeTaskAssignments()
            ->with('taskDefinition')
            ->get();

        // Get existing log for the date
        $existingLog = DailyLog::where('student_id', $studentId)
            ->whereDate('log_date', $logDate)
            ->first();

        // Get weekly task schedules for this date and class (for details)
        $weeklySchedules = WeeklyTaskSchedule::where('class_id', $classId)
            ->where('task_date', $logDate->format('Y-m-d'))
            ->get()
            ->keyBy('class_task_assignment_id');

        $tasks = [];
        foreach ($taskAssignments as $assignment) {
            $definition = $assignment->taskDefinition;
            $existingItem = $existingLog?->items()
                ->where('task_definition_id', $definition->id)
                ->first();

            // Get weekly task details if exists
            $weeklySchedule = $weeklySchedules->get($assignment->id);
            $weeklyTaskDetails = $weeklySchedule?->task_details;

            $tasks[] = [
                'task_id' => $definition->id,
                'class_task_assignment_id' => $assignment->id, // Added for reference
                'task_key' => $definition->name,
                'task_name' => $definition->description,
                'task_type' => $definition->type,
                'task_location' => $definition->task_location,
                'points_weight' => $definition->points_weight,
                'duration_minutes' => $definition->duration_minutes,
                'completed' => $existingItem ? $existingItem->status === 'completed' : false,
                'proof_type' => $existingItem?->proof_type ?? 'none',
                'notes' => $existingItem?->notes,
                'quantity' => $existingItem?->quantity,
                'assignment_order' => $assignment->order,
                // ⭐ جديد: تفاصيل المهمة من الجدول الأسبوعي
                'weekly_task_details' => $weeklyTaskDetails, // null if no schedule exists
            ];
        }

        return [
            'date' => $logDate->format('Y-m-d'),
            'class_id' => $classId,
            'tasks' => $tasks,
            'existing_log' => $existingLog ? [
                'id' => $existingLog->id,
                'status' => $existingLog->status,
                'verified_by' => $existingLog->verified_by,
                'verified_at' => $existingLog->verified_at,
                'notes' => $existingLog->notes,
            ] : null,
        ];
    }

    /**
     * Submit daily log
     */
    public function submitDailyLog(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $studentId = auth()->id();
            $classId = $data['class_id'];
            $logDate = Carbon::parse($data['log_date']);
            $items = $data['items'];

            // Get class to determine finish order
            $class = ClassModel::findOrFail($classId);
            
            // Calculate finish order
            $finishOrder = $this->calculateFinishOrder($classId, $logDate);
            
            // Create or update daily log
            $dailyLog = DailyLog::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'log_date' => $logDate,
                ],
                [
                    'status' => 'submitted',
                    'finish_order' => $finishOrder,
                    'verified_by' => null,
                    'verified_at' => null,
                    'notes' => null,
                ]
            );

            // Create or update log items
            foreach ($items as $item) {
                $taskDefinition = DailyTaskDefinition::where('name', $item['task_key'])->first();
                
                if ($taskDefinition) {
                    DailyLogItem::updateOrCreate(
                        [
                            'daily_log_id' => $dailyLog->id,
                            'task_definition_id' => $taskDefinition->id,
                        ],
                        [
                            'status' => $item['completed'] ? 'completed' : 'pending',
                            'proof_type' => $item['proof_type'] ?? 'none',
                            'notes' => $item['notes'] ?? null,
                            'quantity' => $item['quantity'] ?? 1,
                            'duration_minutes' => $item['duration_minutes'] ?? $taskDefinition->duration_minutes,
                        ]
                    );
                }
            }

            // Calculate and award points
            $points = $this->calculatePoints($dailyLog, $finishOrder);
            
            if ($points > 0) {
                GamificationPoint::create([
                    'student_id' => $studentId,
                    'source_type' => 'daily_log',
                    'source_id' => $dailyLog->id,
                    'points' => $points,
                    'description' => "Daily tasks completion - Order: {$finishOrder}",
                    'awarded_at' => now(),
                ]);
            }

            return [
                'daily_log_id' => $dailyLog->id,
                'finish_order' => $finishOrder,
                'points_awarded' => $points,
                'message' => 'Daily log submitted successfully',
            ];
        });
    }

    /**
     * Calculate finish order for the day
     */
    private function calculateFinishOrder(int $classId, Carbon $logDate): int
    {
        $maxOrder = DailyLog::whereHas('student', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->whereDate('log_date', $logDate)
            ->max('finish_order') ?? 0;

        return $maxOrder + 1;
    }

    /**
     * Calculate points based on completed tasks and finish order
     */
    private function calculatePoints(DailyLog $dailyLog, int $finishOrder): int
    {
        $totalPoints = 0;
        
        // Calculate base points from completed tasks
        foreach ($dailyLog->items as $item) {
            if ($item->status === 'completed') {
                $totalPoints += $item->taskDefinition->points_weight;
            }
        }

        // Add bonus points for top 3 finish orders
        $bonusConfig = config('quran_lms.bonus.ranks');
        if ($finishOrder <= 3 && isset($bonusConfig[$finishOrder])) {
            $totalPoints += $bonusConfig[$finishOrder];
        }

        return $totalPoints;
    }

    /**
     * Get student's daily logs for a date range
     */
    public function getStudentDailyLogs(int $studentId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $logs = DailyLog::where('student_id', $studentId)
            ->whereBetween('log_date', [$start, $end])
            ->with(['items.taskDefinition'])
            ->orderBy('log_date', 'desc')
            ->get();

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'log_date' => $log->log_date->format('Y-m-d'),
                'status' => $log->status,
                'verified_by' => $log->verified_by,
                'verified_at' => $log->verified_at,
                'notes' => $log->notes,
                'items' => $log->items->map(function ($item) {
                    return [
                        'task_key' => $item->taskDefinition->name,
                        'task_name' => $item->taskDefinition->description,
                        'status' => $item->status,
                        'proof_type' => $item->proof_type,
                        'notes' => $item->notes,
                        'quantity' => $item->quantity,
                        'duration_minutes' => $item->duration_minutes,
                        'points_weight' => $item->taskDefinition->points_weight,
                    ];
                }),
            ];
        })->toArray();
    }
}
