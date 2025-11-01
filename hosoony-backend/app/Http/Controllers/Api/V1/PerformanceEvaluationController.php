<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\Session;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceEvaluationController extends Controller
{
    /**
     * Get evaluations for a session.
     */
    public function getSessionEvaluations(Session $session)
    {
        $user = Auth::user();
        
        // Check if user can view this session's evaluations
        if (!$user->hasRole('admin') && $session->teacher_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك عرض تقييمات هذه الجلسة'
            ], 403);
        }

        $evaluations = PerformanceEvaluation::where('session_id', $session->id)
            ->with(['student', 'teacher'])
            ->get();

        return response()->json([
            'success' => true,
            'session' => $session,
            'evaluations' => $evaluations,
        ]);
    }

    /**
     * Store evaluation for a student.
     */
    public function storeEvaluation(Request $request, Session $session)
    {
        $teacher = Auth::user();
        
        // Check if teacher is assigned to this session
        if ($session->teacher_id !== $teacher->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك تقييم هذه الجلسة'
            ], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
            'recitation_score' => 'required|numeric|min:0|max:10',
            'pronunciation_score' => 'required|numeric|min:0|max:10',
            'memorization_score' => 'required|numeric|min:0|max:10',
            'understanding_score' => 'required|numeric|min:0|max:10',
            'participation_score' => 'required|numeric|min:0|max:10',
            'recommendations' => 'nullable|string|max:1000',
            'student_feedback' => 'nullable|string|max:1000',
            'improvement_areas' => 'nullable|string|max:1000',
        ]);

        // Check if student is in the class
        $student = User::findOrFail($request->student_id);
        if (!$session->class->students()->where('user_id', $student->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'الطالبة غير مسجلة في هذا الفصل'
            ], 400);
        }

        // Create or update evaluation
        $evaluation = PerformanceEvaluation::updateOrCreate(
            [
                'session_id' => $session->id,
                'student_id' => $request->student_id,
            ],
            [
                'teacher_id' => $teacher->id,
                'class_id' => $session->class_id,
                'recitation_score' => $request->recitation_score,
                'pronunciation_score' => $request->pronunciation_score,
                'memorization_score' => $request->memorization_score,
                'understanding_score' => $request->understanding_score,
                'participation_score' => $request->participation_score,
                'recommendations' => $request->recommendations,
                'student_feedback' => $request->student_feedback,
                'improvement_areas' => $request->improvement_areas,
                'status' => 'completed',
                'evaluated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التقييم بنجاح',
            'evaluation' => $evaluation->load('student'),
        ]);
    }

    /**
     * Bulk evaluate students for a session.
     */
    public function bulkEvaluate(Request $request, Session $session)
    {
        $teacher = Auth::user();
        
        // Check if teacher is assigned to this session
        if ($session->teacher_id !== $teacher->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك تقييم هذه الجلسة'
            ], 403);
        }

        $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.student_id' => 'required|exists:users,id',
            'evaluations.*.recitation_score' => 'required|numeric|min:0|max:10',
            'evaluations.*.pronunciation_score' => 'required|numeric|min:0|max:10',
            'evaluations.*.memorization_score' => 'required|numeric|min:0|max:10',
            'evaluations.*.understanding_score' => 'required|numeric|min:0|max:10',
            'evaluations.*.participation_score' => 'required|numeric|min:0|max:10',
            'evaluations.*.recommendations' => 'nullable|string|max:1000',
            'evaluations.*.student_feedback' => 'nullable|string|max:1000',
            'evaluations.*.improvement_areas' => 'nullable|string|max:1000',
        ]);

        $evaluations = [];
        
        foreach ($request->evaluations as $evaluationData) {
            $evaluation = PerformanceEvaluation::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $evaluationData['student_id'],
                ],
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $session->class_id,
                    'recitation_score' => $evaluationData['recitation_score'],
                    'pronunciation_score' => $evaluationData['pronunciation_score'],
                    'memorization_score' => $evaluationData['memorization_score'],
                    'understanding_score' => $evaluationData['understanding_score'],
                    'participation_score' => $evaluationData['participation_score'],
                    'recommendations' => $evaluationData['recommendations'] ?? null,
                    'student_feedback' => $evaluationData['student_feedback'] ?? null,
                    'improvement_areas' => $evaluationData['improvement_areas'] ?? null,
                    'status' => 'completed',
                    'evaluated_at' => now(),
                ]
            );
            
            $evaluations[] = $evaluation;
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ جميع التقييمات بنجاح',
            'evaluations_count' => count($evaluations),
        ]);
    }

    /**
     * Get evaluation details.
     */
    public function getEvaluation(PerformanceEvaluation $evaluation)
    {
        $user = Auth::user();
        
        // Check if user can view this evaluation
        if ($evaluation->teacher_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك عرض هذا التقييم'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'evaluation' => $evaluation->load(['student', 'session', 'class']),
            'score_breakdown' => $evaluation->getScoreBreakdown(),
        ]);
    }

    /**
     * Get student's performance history.
     */
    public function getStudentPerformanceHistory(User $student)
    {
        $user = Auth::user();
        
        // Check if user can view this student's performance
        if (!$user->hasRole('admin') && !$user->classes()->whereHas('students', function($query) use ($student) {
            $query->where('user_id', $student->id);
        })->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك عرض أداء هذه الطالبة'
            ], 403);
        }

        $evaluations = PerformanceEvaluation::where('student_id', $student->id)
            ->completed()
            ->with(['session', 'teacher'])
            ->orderBy('evaluated_at', 'desc')
            ->get();

        // Calculate averages
        $weeklyAverage = $this->calculateWeeklyAverage($student);
        $monthlyAverage = $this->calculateMonthlyAverage($student);
        $semesterAverage = $this->calculateSemesterAverage($student);

        return response()->json([
            'success' => true,
            'student' => $student,
            'evaluations' => $evaluations,
            'averages' => [
                'weekly' => $weeklyAverage,
                'monthly' => $monthlyAverage,
                'semester' => $semesterAverage,
            ],
        ]);
    }

    /**
     * Get class performance summary.
     */
    public function getClassPerformanceSummary(ClassModel $class)
    {
        $user = Auth::user();
        
        // Check if user is assigned to this class
        if (!$user->classes()->where('classes.id', $class->id)->exists() && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك عرض أداء هذا الفصل'
            ], 403);
        }

        $students = $class->students()->where('role', 'student')->get();
        $performanceData = [];

        foreach ($students as $student) {
            $evaluations = PerformanceEvaluation::where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->completed()
                ->get();

            $performanceData[] = [
                'student' => $student,
                'total_evaluations' => $evaluations->count(),
                'average_score' => $evaluations->avg('total_score'),
                'last_evaluation' => $evaluations->sortByDesc('evaluated_at')->first(),
                'trend' => $this->calculatePerformanceTrend($evaluations),
            ];
        }

        return response()->json([
            'success' => true,
            'class' => $class,
            'performance_data' => $performanceData,
        ]);
    }

    /**
     * Get student recommendations.
     */
    public function getStudentRecommendations(User $student)
    {
        $user = Auth::user();
        
        // Check if user is the student or has access
        if ($user->id !== $student->id && !$user->hasRole('admin') && !$user->classes()->whereHas('students', function($query) use ($student) {
            $query->where('user_id', $student->id);
        })->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مخول لك عرض توصيات هذه الطالبة'
            ], 403);
        }

        $evaluations = PerformanceEvaluation::where('student_id', $student->id)
            ->completed()
            ->with(['session', 'teacher'])
            ->orderBy('evaluated_at', 'desc')
            ->get();

        // Calculate averages
        $weeklyAverage = $this->calculateWeeklyAverage($student);
        $monthlyAverage = $this->calculateMonthlyAverage($student);
        $semesterAverage = $this->calculateSemesterAverage($student);

        // Calculate performance trend
        $performanceTrend = $this->calculatePerformanceTrend($evaluations);

        // Generate recommendations summary
        $recommendationsSummary = $this->generateRecommendationsSummary($evaluations);

        return response()->json([
            'success' => true,
            'student' => $student,
            'recent_evaluations' => $evaluations->take(5),
            'averages' => [
                'weekly' => $weeklyAverage,
                'monthly' => $monthlyAverage,
                'semester' => $semesterAverage,
            ],
            'performance_trend' => $performanceTrend,
            'recommendations_summary' => $recommendationsSummary,
        ]);
    }

    /**
     * Calculate weekly average for a student.
     */
    private function calculateWeeklyAverage(User $student): float
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return PerformanceEvaluation::where('student_id', $student->id)
            ->completed()
            ->whereBetween('evaluated_at', [$startOfWeek, $endOfWeek])
            ->avg('total_score') ?? 0;
    }

    /**
     * Calculate monthly average for a student.
     */
    private function calculateMonthlyAverage(User $student): float
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return PerformanceEvaluation::where('student_id', $student->id)
            ->completed()
            ->whereBetween('evaluated_at', [$startOfMonth, $endOfMonth])
            ->avg('total_score') ?? 0;
    }

    /**
     * Calculate semester average for a student.
     */
    private function calculateSemesterAverage(User $student): float
    {
        // Assuming semester is 4 months
        $startOfSemester = now()->subMonths(4)->startOfMonth();
        $endOfSemester = now()->endOfMonth();

        return PerformanceEvaluation::where('student_id', $student->id)
            ->completed()
            ->whereBetween('evaluated_at', [$startOfSemester, $endOfSemester])
            ->avg('total_score') ?? 0;
    }

    /**
     * Calculate performance trend.
     */
    private function calculatePerformanceTrend($evaluations): string
    {
        if ($evaluations->count() < 2) {
            return 'stable';
        }

        $recent = $evaluations->take(3)->avg('total_score');
        $older = $evaluations->skip(3)->take(3)->avg('total_score');

        if ($recent > $older + 0.5) {
            return 'improving';
        } elseif ($recent < $older - 0.5) {
            return 'declining';
        }

        return 'stable';
    }

    /**
     * Generate recommendations summary.
     */
    private function generateRecommendationsSummary($evaluations): array
    {
        $summary = [
            'التلاوة' => [],
            'النطق' => [],
            'الحفظ' => [],
            'الفهم' => [],
            'المشاركة' => [],
        ];

        foreach ($evaluations as $evaluation) {
            $breakdown = $evaluation->getScoreBreakdown();
            
            foreach ($breakdown as $key => $score) {
                if ($score['score'] < 7) {
                    $summary[$score['label']][] = $evaluation->improvement_areas ?? 'تحسين الأداء في ' . $score['label'];
                }
            }
        }

        // Remove duplicates and limit recommendations
        foreach ($summary as $category => $recommendations) {
            $summary[$category] = array_unique(array_slice($recommendations, 0, 3));
        }

        return array_filter($summary);
    }
}