<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DailyTasksService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DailyTasksController extends Controller
{
    protected DailyTasksService $dailyTasksService;

    public function __construct(DailyTasksService $dailyTasksService)
    {
        $this->dailyTasksService = $dailyTasksService;
    }

    /**
     * Get daily tasks for a student
     */
    public function getDailyTasks(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'class_id' => 'nullable|integer|exists:classes,id',
        ]);

        // Check if user can access this student's data
        $user = $request->user();
        if (!$user->isAdmin() && $user->id !== $studentId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $this->dailyTasksService->getDailyTasks(
            $studentId,
            $request->date,
            $request->class_id
        );

        return response()->json($data);
    }

    /**
     * Submit daily log
     */
    public function submitDailyLog(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'log_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.task_key' => 'required|string',
            'items.*.completed' => 'required|boolean',
            'items.*.proof_type' => 'nullable|string|in:none,note,audio,video',
            'items.*.notes' => 'nullable|string|max:1000',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.duration_minutes' => 'nullable|integer|min:1',
        ]);

        try {
            $data = $this->dailyTasksService->submitDailyLog($request->all());
            return response()->json($data, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit daily log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student's daily logs for a date range
     */
    public function getStudentDailyLogs(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Check if user can access this student's data
        $user = $request->user();
        if (!$user->isAdmin() && $user->id !== $studentId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = $this->dailyTasksService->getStudentDailyLogs(
            $studentId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'student_id' => $studentId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'logs' => $logs,
        ]);
    }
}