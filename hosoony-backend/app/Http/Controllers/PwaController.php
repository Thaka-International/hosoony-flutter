<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyLog;
use App\Models\Session;
use App\Models\GamificationPoint;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Models\PerformanceEvaluation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PwaController extends Controller
{
    /**
     * Show the home page
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->isStudent()) {
                return redirect()->route('student.dashboard');
            } elseif ($user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
            } elseif ($user->isAdmin()) {
                return redirect()->route('filament.admin.pages.dashboard');
            }
        }
        
        // Redirect to phone login instead of traditional login
        return redirect()->route('phone.login');
    }

    /**
     * Student Dashboard
     */
    public function studentDashboard()
    {
        $user = Auth::user();
        
        // Get today's tasks from class assignments
        $todayTasks = collect();
        if ($user->class_id) {
            $taskAssignments = \App\Models\ClassModel::find($user->class_id)
                ?->activeTaskAssignments()
                ->with('taskDefinition')
                ->get();
            
            $todayTasks = $taskAssignments->map(function ($assignment) {
                return (object) [
                    'id' => $assignment->id,
                    'name' => $assignment->taskDefinition->name,
                    'description' => $assignment->taskDefinition->description,
                    'type' => $assignment->taskDefinition->type,
                    'task_location' => $assignment->taskDefinition->task_location,
                    'points_weight' => $assignment->taskDefinition->points_weight,
                    'duration_minutes' => $assignment->taskDefinition->duration_minutes,
                    'status' => 'pending', // Default status
                    'notes' => $assignment->taskDefinition->description,
                ];
            });
        }

        // Get today's sessions
        $todaySessions = collect();
        if ($user->class_id) {
            $todaySessions = Session::where('class_id', $user->class_id)
                ->whereDate('starts_at', today())
                ->orderBy('starts_at')
                ->get();
        }

        // Get user's points
        $totalPoints = GamificationPoint::where('student_id', $user->id)->sum('points');
        
        // Get user's badges
        $badges = StudentBadge::where('student_id', $user->id)
            ->with('badge')
            ->get();

        // Get user's subscription
        $subscription = Subscription::where('student_id', $user->id)
            ->where('status', 'active')
            ->first();

        return view('pwa.student.dashboard', compact(
            'todayTasks',
            'todaySessions',
            'totalPoints',
            'badges',
            'subscription'
        ));
    }

    /**
     * Student Tasks Page
     */
    public function studentTasks()
    {
        $user = Auth::user();
        
        // Get tasks from class assignments
        $tasks = collect();
        if ($user->class_id) {
            $taskAssignments = \App\Models\ClassModel::find($user->class_id)
                ?->activeTaskAssignments()
                ->with('taskDefinition')
                ->get();
            
            $tasks = $taskAssignments->map(function ($assignment) {
                return (object) [
                    'id' => $assignment->id,
                    'name' => $assignment->taskDefinition->name,
                    'description' => $assignment->taskDefinition->description,
                    'type' => $assignment->taskDefinition->type,
                    'task_location' => $assignment->taskDefinition->task_location,
                    'points_weight' => $assignment->taskDefinition->points_weight,
                    'duration_minutes' => $assignment->taskDefinition->duration_minutes,
                    'status' => 'pending', // Default status
                    'notes' => $assignment->taskDefinition->description,
                ];
            });
        }

        return view('pwa.student.tasks', compact('tasks'));
    }

    /**
     * Student Ranking Page
     */
    public function studentRanking()
    {
        $user = Auth::user();
        
        // Get top 5 students in the same class
        $topStudents = collect();
        if ($user->class_id) {
            $topStudents = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->withSum('gamificationPoints', 'points')
                ->orderBy('gamification_points_sum_points', 'desc')
                ->limit(5)
                ->get();
        }

        // Get current user's position
        $userPosition = 0;
        if ($user->class_id) {
            $userPosition = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->withSum('gamificationPoints', 'points')
                ->orderBy('gamification_points_sum_points', 'desc')
                ->get()
                ->search(function($student) use ($user) {
                    return $student->id === $user->id;
                }) + 1;
        }

        return view('pwa.student.ranking', compact('topStudents', 'userPosition'));
    }

    /**
     * Student Points Page
     */
    public function studentPoints()
    {
        $user = Auth::user();
        
        // Get user's points
        $totalPoints = GamificationPoint::where('student_id', $user->id)->sum('points');
        
        // Get user's badges
        $badges = StudentBadge::where('student_id', $user->id)
            ->with('badge')
            ->get();

        // Get recent points
        $recentPoints = GamificationPoint::where('student_id', $user->id)
            ->orderBy('awarded_at', 'desc')
            ->limit(10)
            ->get();

        return view('pwa.student.points', compact('totalPoints', 'badges', 'recentPoints'));
    }

    /**
     * Student Schedule Page
     */
    public function studentSchedule()
    {
        $user = Auth::user();
        
        $sessions = collect();
        if ($user->class_id) {
            $sessions = Session::where('class_id', $user->class_id)
                ->where('starts_at', '>=', now()->startOfWeek())
                ->where('starts_at', '<=', now()->endOfWeek())
                ->orderBy('starts_at')
                ->get();
        }

        return view('pwa.student.schedule', compact('sessions'));
    }

    /**
     * Student Subscription Page
     */
    public function studentSubscription()
    {
        $user = Auth::user();
        
        // Get user's subscription
        $subscription = Subscription::where('student_id', $user->id)
            ->with('feesPlan')
            ->first();

        // Get user's payments
        $payments = Payment::where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('pwa.student.subscription', compact('subscription', 'payments'));
    }

    /**
     * Teacher Dashboard
     */
    public function teacherDashboard()
    {
        $user = Auth::user();
        
        // Get today's sessions
        $todaySessions = Session::where('teacher_id', $user->id)
            ->whereDate('starts_at', today())
            ->orderBy('starts_at')
            ->with(['class', 'students'])
            ->get();

        // Get this week's sessions
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weekSessions = Session::where('teacher_id', $user->id)
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->with(['class', 'students'])
            ->get();

        // Get pending logs to verify
        $pendingLogs = DailyLog::whereHas('student', function($query) use ($user) {
                $query->where('class_id', $user->class_id);
            })
            ->whereNull('verified_at')
            ->whereDate('log_date', today())
            ->with(['student'])
            ->get();

        // Get recent performance evaluations
        $recentEvaluations = PerformanceEvaluation::where('teacher_id', $user->id)
            ->with(['student', 'session'])
            ->orderBy('evaluated_at', 'desc')
            ->take(5)
            ->get();

        // Get class statistics
        $classStats = [
            'total_students' => 0,
            'active_students' => 0,
            'attendance_today' => 0,
            'average_performance' => 0,
        ];
        
        if ($user->class_id) {
            $totalStudents = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->count();
                
            $activeStudents = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->count();
                
            $attendanceToday = DailyLog::whereHas('student', function($query) use ($user) {
                    $query->where('class_id', $user->class_id);
                })
                ->whereDate('log_date', today())
                ->count();
                
            $avgPerformance = PerformanceEvaluation::whereHas('student', function($query) use ($user) {
                    $query->where('class_id', $user->class_id);
                })
                ->where('evaluated_at', '>=', now()->subDays(7))
                ->avg('total_score') ?? 0;

            $classStats = [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'attendance_today' => $attendanceToday,
                'average_performance' => round($avgPerformance, 1),
            ];
        }

        // Get upcoming sessions (next 7 days)
        $upcomingSessions = Session::where('teacher_id', $user->id)
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addDays(7))
            ->with(['class', 'students'])
            ->orderBy('starts_at')
            ->get();

        // Get notifications for teacher
        $notifications = Notification::where('target_type', 'teacher')
            ->orWhere('target_type', 'all_users')
            ->orWhere('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('pwa.teacher.dashboard', compact(
            'todaySessions',
            'weekSessions',
            'pendingLogs',
            'recentEvaluations',
            'classStats',
            'upcomingSessions',
            'notifications'
        ));
    }

    /**
     * Teacher Timeline Page
     */
    public function teacherTimeline()
    {
        $user = Auth::user();
        
        // Get today's timeline
        $timeline = Session::where('teacher_id', $user->id)
            ->whereDate('starts_at', today())
            ->orderBy('starts_at')
            ->get();

        return view('pwa.teacher.timeline', compact('timeline'));
    }

    /**
     * Teacher Attendance Page
     */
    public function teacherAttendance()
    {
        $user = Auth::user();
        
        // Get students in the class
        $students = collect();
        if ($user->class_id) {
            $students = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->get();
        }

        return view('pwa.teacher.attendance', compact('students'));
    }

    /**
     * Teacher Segments Page
     */
    public function teacherSegments()
    {
        $user = Auth::user();
        
        // Get students in the class
        $students = collect();
        if ($user->class_id) {
            $students = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->get();
        }

        return view('pwa.teacher.segments', compact('students'));
    }

    /**
     * Teacher Reports Page
     */
    public function teacherReports()
    {
        $user = Auth::user();
        
        // Get pending logs to verify
        $pendingLogs = DailyLog::whereHas('student', function($query) use ($user) {
                $query->where('class_id', $user->class_id);
            })
            ->whereNull('verified_at')
            ->whereDate('log_date', today())
            ->with('student')
            ->get();

        return view('pwa.teacher.reports', compact('pendingLogs'));
    }

    /**
     * Teacher Bulk Entry Page
     */
    public function teacherBulkEntry()
    {
        $user = Auth::user();
        
        // Get students in the class
        $students = collect();
        if ($user->class_id) {
            $students = User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->get();
        }

        return view('pwa.teacher.bulk-entry', compact('students'));
    }

    /**
     * Student Recommendations Page
     */
    public function studentRecommendations()
    {
        $user = Auth::user();
        
        // Get recent evaluations
        $recentEvaluations = PerformanceEvaluation::where('student_id', $user->id)
            ->completed()
            ->with(['session', 'teacher'])
            ->orderBy('evaluated_at', 'desc')
            ->take(10)
            ->get();

        // Calculate averages
        $weeklyAverage = $this->calculateWeeklyAverage($user);
        $monthlyAverage = $this->calculateMonthlyAverage($user);
        $semesterAverage = $this->calculateSemesterAverage($user);

        // Calculate performance trend
        $performanceTrend = $this->calculatePerformanceTrend($recentEvaluations);

        // Generate recommendations summary
        $recommendationsSummary = $this->generateRecommendationsSummary($recentEvaluations);

        $averages = [
            'weekly' => $weeklyAverage,
            'monthly' => $monthlyAverage,
            'semester' => $semesterAverage,
        ];

        return view('pwa.student.recommendations', compact(
            'recentEvaluations',
            'averages',
            'performanceTrend',
            'recommendationsSummary'
        ));
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