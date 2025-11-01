<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\DailyLog;
use App\Models\User;
use App\Models\PerformanceMonthly;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportsService
{
    /**
     * Generate daily report data for a class
     */
    public function generateDailyReport(int $classId, string $date): array
    {
        $class = ClassModel::findOrFail($classId);
        $reportDate = Carbon::parse($date);

        // Get students in the class
        $students = User::where('class_id', $classId)
            ->where('role', 'student')
            ->where('status', 'active')
            ->get();

        $reportData = [];
        $totalStudents = $students->count();
        $completedStudents = 0;

        foreach ($students as $student) {
            // Get daily log for the date
            $dailyLog = DailyLog::where('student_id', $student->id)
                ->whereDate('log_date', $reportDate)
                ->with(['items.taskDefinition'])
                ->first();

            $completedTasks = 0;
            $totalTasks = 0;
            $notes = [];

            if ($dailyLog) {
                foreach ($dailyLog->items as $item) {
                    $totalTasks++;
                    if ($item->status === 'completed') {
                        $completedTasks++;
                    }
                    if ($item->notes) {
                        $notes[] = $item->notes;
                    }
                }
            }

            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

            if ($completionRate > 0) {
                $completedStudents++;
            }

            $reportData[] = [
                'student' => $student,
                'daily_log' => $dailyLog,
                'finish_order' => $dailyLog?->finish_order ?? null,
                'completed_tasks' => $completedTasks,
                'total_tasks' => $totalTasks,
                'completion_rate' => $completionRate,
                'notes' => $notes,
                'status' => $dailyLog?->status ?? 'not_submitted',
            ];
        }

        // Sort by finish order
        usort($reportData, function ($a, $b) {
            if ($a['finish_order'] === null && $b['finish_order'] === null) {
                return 0;
            }
            if ($a['finish_order'] === null) {
                return 1;
            }
            if ($b['finish_order'] === null) {
                return -1;
            }
            return $a['finish_order'] <=> $b['finish_order'];
        });

        return [
            'class' => $class,
            'date' => $reportDate,
            'hijri_date' => hijri_display($reportDate),
            'students' => $reportData,
            'summary' => [
                'total_students' => $totalStudents,
                'completed_students' => $completedStudents,
                'completion_rate' => $totalStudents > 0 ? round(($completedStudents / $totalStudents) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Generate monthly report data for a class
     */
    public function generateMonthlyReport(
        int $classId,
        int $month,
        int $year,
        ?int $hijriMonth = null,
        ?int $hijriYear = null
    ): array {
        $class = ClassModel::findOrFail($classId);

        // Get students in the class
        $students = User::where('class_id', $classId)
            ->where('role', 'student')
            ->where('status', 'active')
            ->get();

        $reportData = [];
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        foreach ($students as $student) {
            // Get monthly performance
            $performance = PerformanceMonthly::where('student_id', $student->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            // Get daily logs for the month
            $dailyLogs = DailyLog::where('student_id', $student->id)
                ->whereBetween('log_date', [$monthStart, $monthEnd])
                ->where('status', 'verified')
                ->get();

            $totalDays = $monthStart->diffInDays($monthEnd) + 1;
            $attendedDays = $dailyLogs->count();

            $reportData[] = [
                'student' => $student,
                'performance' => $performance,
                'daily_logs' => $dailyLogs,
                'attendance_days' => $attendedDays,
                'total_days' => $totalDays,
                'attendance_percentage' => $totalDays > 0 ? round(($attendedDays / $totalDays) * 100, 1) : 0,
                'total_points' => $performance?->total_points ?? 0,
                'rank' => $performance?->rank ?? null,
            ];
        }

        // Sort by rank
        usort($reportData, function ($a, $b) {
            if ($a['rank'] === null && $b['rank'] === null) {
                return 0;
            }
            if ($a['rank'] === null) {
                return 1;
            }
            if ($b['rank'] === null) {
                return -1;
            }
            return $a['rank'] <=> $b['rank'];
        });

        return [
            'class' => $class,
            'month' => $month,
            'year' => $year,
            'hijri_month' => $hijriMonth,
            'hijri_year' => $hijriYear,
            'month_name' => $monthStart->format('F Y'),
            'hijri_month_name' => $hijriMonth
                ? $this->getHijriMonthName($hijriMonth) . ' ' . $hijriYear
                : null,
            'students' => $reportData,
            'summary' => [
                'total_students' => $students->count(),
                'average_attendance' => $students->count() > 0
                    ? round(array_sum(array_column($reportData, 'attendance_percentage')) / $students->count(), 1)
                    : 0,
                'average_points' => $students->count() > 0
                    ? round(array_sum(array_column($reportData, 'total_points')) / $students->count(), 1)
                    : 0,
            ],
        ];
    }

    /**
     * Export daily report as CSV
     */
    public function exportDailyReportAsCsv(array $reportData): string
    {
        $csv = "اسم الطالب,ترتيب الإنجاز,المهام المكتملة,إجمالي المهام,نسبة الإكمال,الحالة,الملاحظات\n";

        foreach ($reportData['students'] as $studentData) {
            $csv .= sprintf(
                "%s,%s,%d,%d,%.1f%%,%s,\"%s\"\n",
                $studentData['student']->name,
                $studentData['finish_order'] ?? 'لم يرسل',
                $studentData['completed_tasks'],
                $studentData['total_tasks'],
                $studentData['completion_rate'],
                $this->getStatusText($studentData['status']),
                implode('; ', $studentData['notes'])
            );
        }

        return $csv;
    }

    /**
     * Export monthly report as CSV
     */
    public function exportMonthlyReportAsCsv(array $reportData): string
    {
        $csv = "اسم الطالب,الترتيب,إجمالي النقاط,أيام الحضور,إجمالي الأيام,نسبة الحضور\n";

        foreach ($reportData['students'] as $studentData) {
            $csv .= sprintf(
                "%s,%s,%d,%d,%d,%.1f%%\n",
                $studentData['student']->name,
                $studentData['rank'] ?? 'غير محدد',
                $studentData['total_points'],
                $studentData['attendance_days'],
                $studentData['total_days'],
                $studentData['attendance_percentage']
            );
        }

        return $csv;
    }

    /**
     * Store monthly report and return ID
     */
    public function storeMonthlyReport(array $reportData): string
    {
        $filename = 'monthly_report_' . $reportData['class']->id . '_' . $reportData['year'] . '_' .
            $reportData['month'] . '.json';
        $filepath = 'reports/monthly/' . $filename;

        // Ensure directory exists
        Storage::makeDirectory('reports/monthly');

        // Convert class object to array for JSON storage
        $dataToStore = $reportData;
        $dataToStore['class'] = $reportData['class']->toArray();

        Storage::put($filepath, json_encode($dataToStore, JSON_UNESCAPED_UNICODE));

        // Return just the filename without path for URL safety
        return $filename;
    }

    /**
     * Get stored monthly report
     */
    public function getStoredMonthlyReport(string $filename): array
    {
        $filepath = 'reports/monthly/' . $filename;
        $content = Storage::get($filepath);
        return json_decode($content, true);
    }

    /**
     * Get Hijri month name
     */
    private function getHijriMonthName(int $month): string
    {
        $months = [
            1 => 'محرم', 2 => 'صفر', 3 => 'ربيع الأول', 4 => 'ربيع الثاني',
            5 => 'جمادى الأولى', 6 => 'جمادى الأولى', 7 => 'رجب', 8 => 'شعبان',
            9 => 'رمضان', 10 => 'شوال', 11 => 'ذو القعدة', 12 => 'ذو الحجة'
        ];

        return $months[$month] ?? 'غير محدد';
    }

    /**
     * Get status text in Arabic
     */
    private function getStatusText(string $status): string
    {
        $statuses = [
            'not_submitted' => 'لم يرسل',
            'submitted' => 'مرسل',
            'verified' => 'محقق',
            'rejected' => 'مرفوض',
            'closed' => 'مغلق',
        ];

        return $statuses[$status] ?? $status;
    }
}
